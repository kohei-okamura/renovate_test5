<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Staff;

use Closure;
use Domain\Common\Carbon;
use Domain\Staff\Staff;
use Domain\Staff\StaffEmailVerification;
use Lib\Exceptions\ForbiddenException;
use Lib\Exceptions\NotFoundException;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\GetStaffEmailVerificationUseCaseMixin;
use Tests\Unit\Mixins\LoggerMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\StaffEmailVerificationRepositoryMixin;
use Tests\Unit\Mixins\StaffRepositoryMixin;
use Tests\Unit\Mixins\TransactionManagerMixin;
use Tests\Unit\Test;
use UseCase\Staff\VerifyStaffEmailInteractor;

/**
 * VerifyStaffEmailInteractor のテスト.
 */
final class VerifyStaffEmailInteractorTest extends Test
{
    use CarbonMixin;
    use ContextMixin;
    use ExamplesConsumer;
    use LoggerMixin;
    use GetStaffEmailVerificationUseCaseMixin;
    use MockeryMixin;
    use StaffEmailVerificationRepositoryMixin;
    use StaffRepositoryMixin;
    use TransactionManagerMixin;
    use UnitSupport;

    public const TOKEN = '1234567890abcdefghij1234567890ABCDEFGHIJ12345678901234567890';

    private VerifyStaffEmailInteractor $interactor;
    private StaffEmailVerification $staffEmailVerification;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (VerifyStaffEmailInteractorTest $self): void {
            $self->staffEmailVerification = $self->examples->staffEmailVerifications[0]->copy([
                'staffId' => $self->examples->staffs[0]->id,
            ]);

            $self->context
                ->allows('organization')
                ->andReturn($self->examples->organizations[0])
                ->byDefault();

            $self->staffRepository->allows('lookup')->andReturn(Seq::from($self->examples->staffs[0]))->byDefault();
            $self->staffRepository->allows('store')->andReturn($self->examples->staffs[0])->byDefault();

            $self->staffEmailVerificationRepository
                ->allows('lookupOptionByToken')
                ->andReturn(Option::from($self->staffEmailVerification))
                ->byDefault();

            $self->getStaffEmailVerificationUseCase
                ->allows('handle')
                ->andReturn($self->staffEmailVerification)
                ->byDefault();

            $self->logger->allows('info')->byDefault();

            $self->interactor = app(VerifyStaffEmailInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('update the Staff with email verified', function (): void {
            $this->transactionManager->expects('run')->andReturnUsing(function (Closure $callback) {
                // トランザクションを開始する前に登録処理が行われないことを検証するため
                // `run`に渡されたコールバック関数を呼び出す直前にモックの定義を行う
                // なお、トランザクションの終了後に登録処理が行われないことの検証は（恐らく）できない
                $this->staffRepository
                    ->expects('store')
                    ->withArgs(function (Staff $x) {
                        $values = [
                            'isVerified' => true,
                            'version' => $this->examples->staffs[0]->version + 1,
                            'updatedAt' => Carbon::now(),
                        ];
                        $this->assertModelStrictEquals($x, $this->examples->staffs[0]->copy($values));
                        $this->assertTrue($x->isVerified);
                        return true;
                    })
                    ->andReturn($this->examples->staffs[0]);

                return $callback();
            });

            $this->interactor->handle($this->context, self::TOKEN);
        });
        $this->should('log using info', function (): void {
            $context = ['organizationId' => $this->examples->organizations[0]->id];
            $this->context
                ->expects('logContext')
                ->andReturn($context);
            $this->logger
                ->expects('info')
                ->with('スタッフが更新されました', ['id' => $this->examples->staffs[0]->id] + $context);

            $this->interactor->handle($this->context, self::TOKEN);
        });
        $this->should('throw a NotFoundException when StaffEmailVerification not found', function (): void {
            $this->staffRepository
                ->expects('lookup')
                ->with($this->examples->staffs[0]->id)
                ->andReturn(Seq::emptySeq());

            $this->assertThrows(
                NotFoundException::class,
                function (): void {
                    $this->interactor->handle($this->context, self::TOKEN);
                }
            );
        });
        $this->should('throw a ForbiddenException when the StaffEmailVerification is expired', function (): void {
            $this->assertThrows(
                ForbiddenException::class,
                function (): void {
                    $this->getStaffEmailVerificationUseCase
                        ->allows('handle')
                        ->andThrow(ForbiddenException::class);

                    $this->interactor->handle($this->context, self::TOKEN);
                }
            );
        });
    }
}
