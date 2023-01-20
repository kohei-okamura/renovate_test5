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
use Domain\Common\Pagination;
use Domain\FinderResult;
use Domain\Permission\Permission;
use Domain\Shift\Activity;
use Domain\Shift\ServiceOption;
use Domain\Shift\Task;
use Lib\Exceptions\NotFoundException;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\AttendanceRepositoryMixin;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\FindContractUseCaseMixin;
use Tests\Unit\Mixins\IdentifyContractUseCaseMixin;
use Tests\Unit\Mixins\LoggerMixin;
use Tests\Unit\Mixins\LookupAttendanceUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\OrganizationRepositoryMixin;
use Tests\Unit\Mixins\TransactionManagerMixin;
use Tests\Unit\Test;
use UseCase\Shift\EditAttendanceInteractor;

/**
 * {@link \UseCase\Shift\EditAttendanceInteractor} のテスト.
 */
final class EditAttendanceInteractorTest extends Test
{
    use CarbonMixin;
    use ContextMixin;
    use ExamplesConsumer;
    use FindContractUseCaseMixin;
    use IdentifyContractUseCaseMixin;
    use LoggerMixin;
    use LookupAttendanceUseCaseMixin;
    use MockeryMixin;
    use AttendanceRepositoryMixin;
    use OrganizationRepositoryMixin;
    use TransactionManagerMixin;
    use UnitSupport;

    private EditAttendanceInteractor $interactor;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (EditAttendanceInteractorTest $self): void {
            $self->findContractUseCase
                ->allows('handle')
                ->andReturn(FinderResult::from(Seq::from($self->examples->contracts[0]), Pagination::create()))
                ->byDefault();
            $self->identifyContractUseCase
                ->allows('handle')
                ->andReturn(Option::from($self->examples->contracts[0]))
                ->byDefault();
            $self->logger
                ->allows('info')
                ->andReturnNull()
                ->byDefault();
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
            $self->organizationRepository
                ->allows('lookupOptionByCode')
                ->andReturn(Seq::from($self->examples->organizations[0]))
                ->byDefault();
            $self->interactor = app(EditAttendanceInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('edit the attendance after transaction begun', function (): void {
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

            $this->interactor->handle($this->context, $this->examples->attendances[0]->id, $this->getEditValue());
        });
        $this->should('return the Result', function (): void {
            $this->assertModelStrictEquals(
                $this->examples->attendances[0],
                $this->interactor->handle($this->context, $this->examples->attendances[0]->id, $this->getEditValue())
            );
        });
        $this->should('throw a NotFoundException when the id not exists in db', function (): void {
            $this->lookupAttendanceUseCase
                ->expects('handle')
                ->with($this->context, Permission::updateAttendances(), self::NOT_EXISTING_ID)
                ->andReturn(Seq::emptySeq());

            $this->assertThrows(
                NotFoundException::class,
                function (): void {
                    $this->interactor->handle($this->context, self::NOT_EXISTING_ID, $this->getEditValue());
                }
            );
        });
        $this->should('set organizationId when userId is null', function (): void {
            $attendance = $this->examples->attendances[0]->copy(['userId' => null]);
            $this->attendanceRepository
                ->expects('store')
                ->with(equalTo($attendance->copy([
                    'contractId' => null,
                    'updatedAt' => Carbon::now(),
                ])))
                ->andReturn($this->examples->attendances[0]);

            $this->interactor->handle($this->context, $attendance->id, $attendance->toAssoc());
        });
        $this->should('set organizationId and contractId when userId is not null', function (): void {
            $attendance = $this->examples->attendances[0]->copy(['task' => Task::dwsVisitingCareForPwsd()]);
            $this->attendanceRepository
                ->expects('store')
                ->with(equalTo($attendance->copy([
                    'contractId' => $this->examples->contracts[0]->id,
                    'updatedAt' => Carbon::now(),
                ])))
                ->andReturn($attendance);

            $this->interactor->handle($this->context, $attendance->id, $attendance->toAssoc());
        });
        $this->should('use IdentifyContractUseCase when userId is not null', function (): void {
            $attendance = $this->examples->attendances[0]->copy(['task' => Task::dwsVisitingCareForPwsd()]);
            $this->identifyContractUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    Permission::updateShifts(),
                    $attendance->officeId,
                    $attendance->userId,
                    $attendance->task->toServiceSegment()->get(),
                    equalTo(Carbon::now())
                )
                ->andReturn(Option::from($this->examples->contracts[0]));

            $this->interactor->handle($this->context, $attendance->id, $attendance->toAssoc());
        });
        $this->should('throw NotFoundException when IdentifyContractUseCase return none', function (): void {
            $attendance = $this->examples->attendances[0]->copy(['task' => Task::dwsVisitingCareForPwsd()]);
            $this->identifyContractUseCase
                ->expects('handle')
                ->andReturn(Option::none());

            $this->assertThrows(NotFoundException::class, function () use ($attendance): void {
                $this->interactor->handle($this->context, $attendance->id, $attendance->toAssoc());
            });
        });
        $this->should('log using info', function (): void {
            $context = [
                'organizationId' => $this->examples->organizations[0]->id,
                'staffId' => $this->examples->staffs[0]->id,
            ];
            $this->context
                ->expects('logContext')
                ->andReturn($context);
            $this->logger
                ->expects('info')
                ->with('勤務実績が更新されました', ['id' => $this->examples->attendances[0]->id] + $context);

            $this->interactor->handle($this->context, $this->examples->attendances[0]->id, $this->getEditValue());
        });
    }

    /**
     * 編集情報を取得する.
     *
     * @return array
     */
    public function getEditValue(): array
    {
        return [
            'task' => Task::ltcsPhysicalCare(),
            'serviceCode' => '123456',
            'userId' => $this->examples->attendances[0]->userId,
            'officeId' => $this->examples->attendances[0]->officeId,
            'contractId' => $this->examples->attendances[0]->contractId,
            'assignerId' => $this->examples->attendances[0]->contractId,
            'assignees' => [
                [
                    'staffId' => $this->examples->staffs[0]->id,
                    'isUndecided' => false,
                    'isTraining' => false,
                ],
                [
                    'staffId' => $this->examples->staffs[1]->id,
                    'isUndecided' => false,
                    'isTraining' => true,
                ],
            ],
            'headcount' => 2,
            'start' => Carbon::now()->format('Y-m-d 10:00:00'),
            'end' => Carbon::now()->format('Y-m-d 11:00:00'),
            'date' => $this->examples->attendances[0]->schedule->date,
            'durations' => [
                [
                    'activity' => Activity::ltcsPhysicalCare()->value(),
                    'duration' => 60,
                ],
            ],
            'options' => [
                ServiceOption::firstTime()->value(),
            ],
            'note' => '備考',
            'isConfirmed' => false,
        ];
    }
}
