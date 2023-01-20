<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Staff;

use App\Concretes\PermanentDatabaseTransactionManager;
use Domain\Common\Carbon;
use Domain\Staff\CreateStaffPasswordResetEvent;
use Domain\Staff\StaffPasswordReset;
use Lib\Exceptions\RuntimeException;
use Mockery;
use ScalikePHP\Option;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ConfigMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\EventDispatcherMixin;
use Tests\Unit\Mixins\IdentifyStaffByEmailUseCaseMixin;
use Tests\Unit\Mixins\LoggerMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\StaffPasswordResetRepositoryMixin;
use Tests\Unit\Mixins\TokenMakerMixin;
use Tests\Unit\Mixins\TransactionManagerMixin;
use Tests\Unit\Test;
use UseCase\Staff\CreateStaffPasswordResetInteractor;

/**
 * CreateStaffPasswordResetInteractor のテスト.
 */
final class CreateStaffPasswordResetInteractorTest extends Test
{
    use CarbonMixin;
    use ConfigMixin;
    use ContextMixin;
    use EventDispatcherMixin;
    use ExamplesConsumer;
    use LoggerMixin;
    use MockeryMixin;
    use StaffPasswordResetRepositoryMixin;
    use IdentifyStaffByEmailUseCaseMixin;
    use TokenMakerMixin;
    use TransactionManagerMixin;
    use UnitSupport;

    public const TOKEN = '1234567890abcdefghij1234567890ABCDEFGHIJ12345678901234567890';

    private CreateStaffPasswordResetInteractor $interactor;
    private StaffPasswordReset $staffPasswordReset;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->staffPasswordReset = $self->examples->staffPasswordResets[0]->copy([
                'staffId' => $self->examples->staffs[0]->id,
            ]);

            $self->logger->allows('info')->byDefault();

            $self->config->allows('get')->with('zinger.password_reset.lifetime_minutes')->andReturn(1440)->byDefault();
            $self->context->allows('organization')->andReturn($self->examples->organizations[0]);
            $self->identifyStaffByEmailUseCase->allows('handle')->andReturn(Option::from($self->examples->staffs[0]))->byDefault();

            $self->staffPasswordResetRepository->allows('lookupOptionByToken')->andReturn(Option::none())->byDefault();
            $self->staffPasswordResetRepository->allows('store')->andReturn($self->staffPasswordReset)->byDefault();
            $self->staffPasswordResetRepository
                ->allows('transactionManager')
                ->andReturn(PermanentDatabaseTransactionManager::class)
                ->byDefault();

            $self->tokenMaker->allows('make')->andReturn(self::TOKEN)->byDefault();
            $self->eventDispatcher->allows('dispatch')->andReturnNull()->byDefault();

            $self->interactor = app(CreateStaffPasswordResetInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('log using info', function (): void {
            $context = ['organizationId' => $this->examples->organizations[0]->id];
            $this->context
                ->expects('logContext')
                ->andReturn($context);
            $this->logger
                ->expects('info')
                ->with('スタッフパスワード再設定が登録されました', ['id' => $this->examples->staffs[0]->id] + $context);

            $this->interactor->handle($this->context, 'sample@example.com');
        });
        $this->should('store the StaffPasswordReset when staff exists in db', function (): void {
            $this->staffPasswordResetRepository
                ->expects('store')
                ->withArgs(function (StaffPasswordReset $x) {
                    return $x->staffId === $this->examples->staffs[0]->id
                        && $x->email === $this->examples->staffs[0]->email
                        && strlen($x->token) === 60
                        && $x->expiredAt->equalTo(Carbon::tomorrow())
                        && $x->createdAt->equalTo(Carbon::now());
                })
                ->andReturn($this->staffPasswordReset);

            $this->interactor->handle($this->context, $this->examples->staffs[0]->email);
        });
        $this->should(
            'set expiredAt using config',
            function (int $lifetime): void {
                $this->config
                    ->expects('get')
                    ->with('zinger.password_reset.lifetime_minutes')
                    ->andReturn($lifetime);
                $this->staffPasswordResetRepository
                    ->expects('store')
                    ->with(Mockery::capture($actual))
                    ->andReturn($this->staffPasswordReset);

                $this->interactor->handle($this->context, $this->examples->staffs[0]->email);

                $this->assertSame(
                    Carbon::now()->startOfMinute()->addMinutes($lifetime)->timestamp,
                    $actual->expiredAt->timestamp
                );
            },
            ['examples' => [[120], [30], [1440], [90]]]
        );
        $this->should(
            'throw a RuntimeException when failed to make unique token',
            function (): void {
                $this->staffPasswordResetRepository
                    ->allows('lookupOptionByToken')
                    ->andReturn(Option::from($this->staffPasswordReset));

                $this->assertThrows(
                    RuntimeException::class,
                    function (): void {
                        $this->interactor->handle($this->context, 'sample@example.com');
                    }
                );
            }
        );
        $this->should(
            'make a token when it is required',
            function (): void {
                $this->tokenMaker->expects('make')->times(50)->andReturn(self::TOKEN);
                $this->staffPasswordResetRepository
                    ->expects('lookupOptionByToken')
                    ->times(49)
                    ->andReturn(Option::from($this->staffPasswordReset));
                $this->staffPasswordResetRepository
                    ->expects('lookupOptionByToken')
                    ->andReturn(Option::none());

                $this->interactor->handle($this->context, 'sample@example.com');
            }
        );
        $this->should('use EventDispatcher', function (): void {
            $this->eventDispatcher
                ->expects('dispatch')
                ->with(equalTo(new CreateStaffPasswordResetEvent($this->context, $this->staffPasswordReset)))
                ->andReturnNull();
            $this->interactor->handle($this->context, 'sample@example.com');
        });
        $this->should('not use EventDispatcher when staff not exists in db', function (): void {
            $this->identifyStaffByEmailUseCase->allows('handle')->andReturn(Option::none());
            $this->eventDispatcher->shouldNotHaveBeenCalled(['dispatch']);
            $this->interactor->handle($this->context, 'nobody@example.com');
        });
    }
}
