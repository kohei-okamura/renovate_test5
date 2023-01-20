<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Staff;

use Closure;
use Domain\Common\Carbon;
use Domain\Staff\Staff;
use Domain\Staff\StaffPasswordReset;
use Lib\Exceptions\ForbiddenException;
use Lib\Exceptions\NotFoundException;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\GetStaffPasswordResetUseCaseMixin;
use Tests\Unit\Mixins\LoggerMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\StaffPasswordResetRepositoryMixin;
use Tests\Unit\Mixins\StaffRepositoryMixin;
use Tests\Unit\Mixins\TransactionManagerMixin;
use Tests\Unit\Test;
use UseCase\Staff\ResetStaffPasswordInteractor;

/**
 * ResetStaffPasswordInteractor のテスト.
 */
final class ResetStaffPasswordInteractorTest extends Test
{
    use CarbonMixin;
    use ContextMixin;
    use ExamplesConsumer;
    use LoggerMixin;
    use GetStaffPasswordResetUseCaseMixin;
    use MockeryMixin;
    use StaffPasswordResetRepositoryMixin;
    use StaffRepositoryMixin;
    use TransactionManagerMixin;
    use UnitSupport;

    public const TOKEN = '1234567890abcdefghij1234567890ABCDEFGHIJ12345678901234567890';

    private ResetStaffPasswordInteractor $interactor;
    private StaffPasswordReset $staffPasswordReset;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (ResetStaffPasswordInteractorTest $self): void {
            $self->staffPasswordReset = $self->examples->staffPasswordResets[0]->copy([
                'staffId' => $self->examples->staffs[0]->id,
            ]);

            $self->context
                ->allows('organization')
                ->andReturn($self->examples->organizations[0])
                ->byDefault();

            $self->staffRepository->allows('lookup')->andReturn(Seq::from($self->examples->staffs[0]))->byDefault();
            $self->staffRepository->allows('store')->andReturn($self->examples->staffs[0])->byDefault();

            $self->staffPasswordResetRepository
                ->allows('lookupOptionByToken')
                ->andReturn(Option::from($self->staffPasswordReset))
                ->byDefault();

            $self->getStaffPasswordResetUseCase
                ->allows('handle')
                ->andReturn($self->staffPasswordReset)
                ->byDefault();

            $self->logger->allows('info')->byDefault();

            $self->interactor = app(ResetStaffPasswordInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('update the Staff with new password', function (): void {
            $this->transactionManager->expects('run')->andReturnUsing(function (Closure $callback) {
                // トランザクションを開始する前に登録処理が行われないことを検証するため
                // `run`に渡されたコールバック関数を呼び出す直前にモックの定義を行う
                // なお、トランザクションの終了後に登録処理が行われないことの検証は（恐らく）できない
                $this->staffRepository
                    ->expects('store')
                    ->withArgs(function (Staff $x) {
                        $values = [
                            'password' => $x->password,
                            'version' => $this->examples->staffs[0]->version + 1,
                            'updatedAt' => Carbon::now(),
                        ];
                        $this->assertModelStrictEquals($x, $this->examples->staffs[0]->copy($values));
                        $this->assertTrue(password_verify('PassWORD', $x->password->hashString()));
                        return true;
                    })
                    ->andReturn($this->examples->staffs[0]);

                return $callback();
            });

            $this->interactor->handle($this->context, self::TOKEN, 'PassWORD');
        });
        $this->should('log using info', function (): void {
            $context = ['organizationId' => $this->examples->organizations[0]->id];
            $this->context
                ->expects('logContext')
                ->andReturn($context);
            $this->logger
                ->expects('info')
                ->with('スタッフパスワードが更新されました', ['id' => $this->examples->staffs[0]->id] + $context);

            $this->interactor->handle($this->context, self::TOKEN, 'PassWORD');
        });

        $this->should('throw a NotFoundException when StaffPasswordReset not found', function (): void {
            $this->assertThrows(
                NotFoundException::class,
                function (): void {
                    $this->staffRepository
                        ->expects('lookup')
                        ->with($this->examples->staffs[0]->id)
                        ->andReturn(Seq::emptySeq());

                    $this->interactor->handle($this->context, self::TOKEN, 'PassWORD');
                }
            );
        });
        $this->should('throw a ForbiddenException when the StaffPasswordReset is expired', function (): void {
            $this->assertThrows(
                ForbiddenException::class,
                function (): void {
                    $this->getStaffPasswordResetUseCase
                        ->allows('handle')
                        ->andThrow(ForbiddenException::class);

                    $this->interactor->handle($this->context, self::TOKEN, 'PassWORD');
                }
            );
        });
    }
}
