<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Shift;

use Closure;
use Domain\Permission\Permission;
use Domain\Shift\Attendance;
use Lib\Exceptions\NotFoundException;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\AttendanceRepositoryMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\LoggerMixin;
use Tests\Unit\Mixins\LookupAttendanceUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\TransactionManagerMixin;
use Tests\Unit\Test;
use UseCase\Shift\CancelAttendanceInteractor;

/**
 * {@link \UseCase\Shift\CancelAttendanceInteractor} Test.
 */
class CancelAttendanceInteractorTest extends Test
{
    use AttendanceRepositoryMixin;
    use ContextMixin;
    use ExamplesConsumer;
    use LoggerMixin;
    use LookupAttendanceUseCaseMixin;
    use MockeryMixin;
    use TransactionManagerMixin;
    use UnitSupport;

    private CancelAttendanceInteractor $interactor;
    private string $reason = 'キャンセル理由';

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (CancelAttendanceInteractorTest $self): void {
            $self->logger
                ->allows('info')
                ->byDefault();

            $self->lookupAttendanceUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->attendances[1]))
                ->byDefault();
            $self->attendanceRepository
                ->allows('store')
                ->andReturn($self->examples->attendances[1])
                ->byDefault();

            $self->interactor = app(CancelAttendanceInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('update the Attendance after transaction begun', function (): void {
            $this->transactionManager
                ->expects('run')
                ->andReturnUsing(function (Closure $callback) {
                    // トランザクションを開始する前に削除処理が行われないことを検証するため
                    // `run` に渡されたコールバック関数を呼び出す直前にモックの定義を行う
                    // なお、トランザクションの終了後に削除処理が行われないことの検証は（恐らく）できない
                    $this->attendanceRepository
                        ->expects('store')
                        ->andReturnUsing(function (Attendance $attendance): Attendance {
                            $this->assertTrue($attendance->isCanceled);
                            $this->assertSame($this->reason, $attendance->reason);
                            return $attendance;
                        });
                    return $callback();
                });

            $this->interactor->handle($this->context, $this->reason, $this->examples->attendances[1]->id);
        });
        $this->should('use LookupAttendanceUseCase', function (): void {
            $this->lookupAttendanceUseCase
                ->expects('handle')
                ->with($this->context, Permission::updateAttendances(), $this->examples->attendances[1]->id)
                ->andReturn(Seq::from($this->examples->attendances[1]));

            $this->interactor->handle($this->context, $this->reason, $this->examples->attendances[1]->id);
        });
        $this->should('throw NotFoundException when id is canceled', function (): void {
            $attendance = $this->examples->attendances[6];
            $this->lookupAttendanceUseCase
                ->expects('handle')
                ->andReturn(Seq::from($attendance->copy(['isCanceled' => true])));

            $this->assertThrows(
                NotFoundException::class,
                function () use ($attendance): void {
                    $this->interactor->handle($this->context, $this->reason, $attendance->id);
                }
            );
        });
    }
}
