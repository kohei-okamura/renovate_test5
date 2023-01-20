<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Shift;

use App\Concretes\PermanentDatabaseTransactionManager;
use Closure;
use Domain\Permission\Permission;
use Lib\Exceptions\NotFoundException;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\AttendanceRepositoryMixin;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\LoggerMixin;
use Tests\Unit\Mixins\LookupAttendanceUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\OrganizationRepositoryMixin;
use Tests\Unit\Mixins\TransactionManagerMixin;
use Tests\Unit\Test;
use UseCase\Shift\ConfirmAttendanceInteractor;

/**
 * {@link \UseCase\Shift\ConfirmAttendanceInteractor} のテスト.
 */
final class ConfirmAttendanceInteractorTest extends Test
{
    use CarbonMixin;
    use ContextMixin;
    use ExamplesConsumer;
    use LoggerMixin;
    use LookupAttendanceUseCaseMixin;
    use MockeryMixin;
    use AttendanceRepositoryMixin;
    use OrganizationRepositoryMixin;
    use TransactionManagerMixin;
    use UnitSupport;

    private ConfirmAttendanceInteractor $interactor;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (ConfirmAttendanceInteractorTest $self): void {
            $self->lookupAttendanceUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->attendances[0]))
                ->byDefault();
            $self->attendanceRepository
                ->allows('store')
                ->andReturn($self->examples->attendances[0])
                ->byDefault();
            $self->attendanceRepository
                ->allows('transactionManager')
                ->andReturn(PermanentDatabaseTransactionManager::class)
                ->passthru();
            $self->logger
                ->allows('info')
                ->byDefault();
            $self->interactor = app(ConfirmAttendanceInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('confirm the attendance after transaction begun', function (): void {
            $this->lookupAttendanceUseCase
                ->expects('handle')
                ->with($this->context, Permission::updateAttendances(), $this->examples->attendances[0]->id)
                ->andReturn(Seq::from($this->examples->attendances[0]));
            $this->transactionManager
                ->expects('run')
                ->andReturnUsing(function (Closure $callback) {
                    // トランザクションを開始する前に編集処理が行われないことを検証するため
                    // `run` に渡されたコールバック関数を呼び出す直前にモックの定義を行う
                    // なお、トランザクションの終了後に編集処理が行われないことの検証は（恐らく）できない
                    $this->attendanceRepository
                        ->expects('store')
                        ->andReturn($this->examples->attendances[0]);
                    return $callback();
                });

            $this->interactor->handle($this->context, ...[$this->examples->attendances[0]->id]);
        });
        $this->should('throw a NotFoundException when the id not exists in db', function (): void {
            $this->lookupAttendanceUseCase
                ->expects('handle')
                ->with($this->context, Permission::updateAttendances(), self::NOT_EXISTING_ID)
                ->andReturn(Seq::emptySeq());

            $this->assertThrows(
                NotFoundException::class,
                function (): void {
                    $this->interactor->handle($this->context, ...[self::NOT_EXISTING_ID]);
                }
            );
        });
    }
}
