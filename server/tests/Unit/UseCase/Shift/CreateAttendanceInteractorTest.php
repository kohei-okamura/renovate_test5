<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Shift;

use App\Concretes\PermanentDatabaseTransactionManager;
use Closure;
use Domain\Common\Carbon;
use Domain\Permission\Permission;
use Domain\Shift\Task;
use Lib\Exceptions\NotFoundException;
use ScalikePHP\Option;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\AttendanceRepositoryMixin;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\IdentifyContractUseCaseMixin;
use Tests\Unit\Mixins\LoggerMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\TransactionManagerMixin;
use Tests\Unit\Test;
use UseCase\Shift\CreateAttendanceInteractor;

/**
 * {@link \UseCase\Shift\CreateAttendanceInteractor} のテスト.
 */
final class CreateAttendanceInteractorTest extends Test
{
    use CarbonMixin;
    use ContextMixin;
    use ExamplesConsumer;
    use IdentifyContractUseCaseMixin;
    use LoggerMixin;
    use MockeryMixin;
    use AttendanceRepositoryMixin;
    use TransactionManagerMixin;
    use UnitSupport;

    private CreateAttendanceInteractor $interactor;

    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (CreateAttendanceInteractorTest $self): void {
            $self->identifyContractUseCase
                ->allows('handle')
                ->andReturn(Option::from($self->examples->contracts[0]))
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
                ->andReturnNull()
                ->byDefault();

            $self->interactor = app(CreateAttendanceInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('store the Attendance after transaction begun', function (): void {
            $this->transactionManager
                ->expects('run')
                ->andReturnUsing(function (Closure $callback) {
                    // トランザクションを開始する前に登録処理が行われないことを検証するため
                    // `run`に渡されたコールバック関数を呼び出す直前にモックの定義を行う
                    // なお、トランザクションの終了後に登録処理が行われないことの検証は（恐らく）できない
                    $this->attendanceRepository->expects('store')->andReturn($this->examples->attendances[0]);
                    return $callback();
                });

            $this->interactor->handle(
                $this->context,
                $this->examples->attendances[0]->copy(['task' => Task::dwsVisitingCareForPwsd()])
            );
        });
        $this->should('set organizationId when userId is null', function (): void {
            $attendance = $this->examples->attendances[0]->copy(['userId' => null]);
            $this->attendanceRepository
                ->expects('store')
                ->with(equalTo($attendance->copy([
                    'organizationId' => $this->context->organization->id,
                    'contractId' => null,
                ])))
                ->andReturn($attendance);

            $this->interactor->handle($this->context, $attendance);
        });
        $this->should('set organizationId and contractId when userId is not null', function (): void {
            $attendance = $this->examples->attendances[0]->copy(['task' => Task::dwsVisitingCareForPwsd()]);
            $this->attendanceRepository
                ->expects('store')
                ->with(equalTo($attendance->copy([
                    'organization_id' => $this->context->organization->id,
                    'contractId' => $this->examples->contracts[0]->id,
                ])))
                ->andReturn($attendance);

            $this->interactor->handle($this->context, $attendance);
        });
        $this->should('use IdentifyContractUseCase when userId is not null', function (): void {
            $contract = $this->examples->contracts[0];
            $attendance = $this->examples->attendances[4];
            $this->attendanceRepository
                ->expects('store')
                ->with(equalTo($attendance->copy([
                    'organizationId' => $this->context->organization->id,
                    'contractId' => $contract->id,
                ])))
                ->andReturn($this->examples->attendances[0]);

            $this->identifyContractUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    Permission::createShifts(),
                    $attendance->officeId,
                    $attendance->userId,
                    $attendance->task->toServiceSegment()->get(),
                    equalTo(Carbon::now())
                )
                ->andReturn(Option::from($contract));

            $this->interactor->handle($this->context, $attendance);
        });
        $this->should('throw NotFoundException when IdentifyContractUseCase return none', function (): void {
            $this->identifyContractUseCase
                ->expects('handle')
                ->andReturn(Option::none());

            $this->assertThrows(NotFoundException::class, function (): void {
                $this->interactor->handle(
                    $this->context,
                    $this->examples->attendances[0]->copy(['task' => Task::dwsVisitingCareForPwsd()])
                );
            });
        });
        $this->should('return the attendance', function (): void {
            $this->assertEquals(
                $this->examples->attendances[0],
                $this->interactor->handle(
                    $this->context,
                    $this->examples->attendances[0]->copy(['task' => Task::dwsVisitingCareForPwsd()])
                )
            );
        });
        $this->should('use logger output log', function (): void {
            $this->logger
                ->expects('info')
                ->with('勤務実績が登録されました', typeOf('array'))
                ->andReturnNull();

            $this->assertNotNull($this->interactor->handle(
                $this->context,
                $this->examples->attendances[0]->copy(['task' => Task::dwsVisitingCareForPwsd()])
            ));
        });
    }
}
