<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Shift;

use App\Concretes\PermanentDatabaseTransactionManager;
use Closure;
use Domain\Common\Carbon;
use Domain\Permission\Permission;
use Domain\Shift\Activity;
use Domain\Shift\Assignee;
use Domain\Shift\ServiceOption;
use Domain\Shift\Task;
use Domain\Shift\UpdateShiftEvent;
use Lib\Exceptions\NotFoundException;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\EventDispatcherMixin;
use Tests\Unit\Mixins\IdentifyContractUseCaseMixin;
use Tests\Unit\Mixins\LoggerMixin;
use Tests\Unit\Mixins\LookupShiftUseCaseMixin;
use Tests\Unit\Mixins\LookupStaffUseCaseMixin;
use Tests\Unit\Mixins\LookupUserUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\OrganizationRepositoryMixin;
use Tests\Unit\Mixins\ShiftRepositoryMixin;
use Tests\Unit\Mixins\TransactionManagerMixin;
use Tests\Unit\Test;
use UseCase\Shift\EditShiftInteractor;

class EditShiftInteractorTest extends Test
{
    use CarbonMixin;
    use ContextMixin;
    use EventDispatcherMixin;
    use ExamplesConsumer;
    use IdentifyContractUseCaseMixin;
    use LoggerMixin;
    use LookupStaffUseCaseMixin;
    use LookupShiftUseCaseMixin;
    use LookupUserUseCaseMixin;
    use MockeryMixin;
    use ShiftRepositoryMixin;
    use OrganizationRepositoryMixin;
    use TransactionManagerMixin;
    use UnitSupport;

    private EditShiftInteractor $interactor;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (EditShiftInteractorTest $self): void {
            $self->identifyContractUseCase
                ->allows('handle')
                ->andReturn(Option::from($self->examples->contracts[0]))
                ->byDefault();
            $self->eventDispatcher
                ->allows('dispatch')
                ->andReturnNull()
                ->byDefault();
            $self->lookupStaffUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->staffs[0]))
                ->byDefault();
            $self->lookupShiftUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->shifts[4]))
                ->byDefault();
            $self->lookupUserUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->users[0]))
                ->byDefault();
            $self->shiftRepository
                ->allows('store')
                ->andReturn($self->examples->shifts[0])
                ->byDefault();
            $self->shiftRepository
                ->allows('transactionManager')
                ->andReturn(PermanentDatabaseTransactionManager::class)
                ->passthru();
            $self->organizationRepository
                ->allows('lookupOptionByCode')
                ->andReturn(Seq::from($self->examples->organizations[0]))
                ->byDefault();
            $self->logger
                ->allows('info')
                ->byDefault();
            $self->interactor = app(EditShiftInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('edit the shift after transaction begun', function (): void {
            $this->lookupShiftUseCase
                ->expects('handle')
                ->with($this->context, Permission::updateShifts(), $this->examples->shifts[0]->id)
                ->andReturn(Seq::from($this->examples->shifts[0]));
            $this->transactionManager
                ->expects('run')
                ->andReturnUsing(function (Closure $callback) {
                    // トランザクションを開始する前に編集処理が行われないことを検証するため
                    // `run` に渡されたコールバック関数を呼び出す直前にモックの定義を行う
                    // なお、トランザクションの終了後に編集処理が行われないことの検証は（恐らく）できない
                    $this->shiftRepository
                        ->expects('store')
                        ->andReturn($this->examples->shifts[0]);
                    return $callback();
                });

            $this->interactor->handle($this->context, $this->examples->shifts[0]->id, $this->getEditValue());
        });
        $this->should('return the Shift', function (): void {
            $this->assertModelStrictEquals(
                $this->examples->shifts[0],
                $this->interactor->handle($this->context, $this->examples->shifts[0]->id, $this->getEditValue())
            );
        });
        $this->should('use LookupShiftUseCase', function (): void {
            $shift = $this->examples->shifts[0];
            $this->lookupShiftUseCase
                ->expects('handle')
                ->with($this->context, Permission::updateShifts(), $shift->id)
                ->andReturn(Seq::from($shift));

            $this->interactor->handle($this->context, $shift->id, $shift->toAssoc());
        });
        $this->should('use IdentifyContractUseCase when userId is not null', function (): void {
            $shift = $this->examples->shifts[0];
            $this->identifyContractUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    Permission::updateShifts(),
                    $shift->officeId,
                    $shift->userId,
                    $shift->task->toServiceSegment()->get(),
                    equalTo(Carbon::now())
                )
                ->andReturn(Option::from($this->examples->contracts[0]));

            $this->interactor->handle($this->context, $shift->id, $shift->toAssoc());
        });
        $this->should('throw NotFoundException when IdentifyContractUseCase return none', function (): void {
            $shift = $this->examples->shifts[0];
            $this->identifyContractUseCase
                ->expects('handle')
                ->andReturn(Option::none());

            $this->assertThrows(NotFoundException::class, function () use ($shift): void {
                $this->interactor->handle($this->context, $shift->id, $shift->toAssoc());
            });
        });
        $this->should('log using info', function (): void {
            $context = ['organizationId' => $this->examples->organizations[0]->id];
            $this->context
                ->expects('logContext')
                ->andReturn($context);
            $this->logger
                ->expects('info')
                ->with('勤務シフトが更新されました', ['id' => $this->examples->shifts[0]->id] + $context);

            $this->interactor->handle($this->context, $this->examples->shifts[0]->id, $this->getEditValue());
        });
        $this->should('throw a NotFoundException when the id not exists in db', function (): void {
            $this->lookupShiftUseCase
                ->expects('handle')
                ->with($this->context, Permission::updateShifts(), self::NOT_EXISTING_ID)
                ->andReturn(Seq::emptySeq());

            $this->assertThrows(
                NotFoundException::class,
                function (): void {
                    $this->interactor->handle($this->context, self::NOT_EXISTING_ID, $this->getEditValue());
                }
            );
        });
        $this->should('throw NotFoundException when lookupStaffUseCase return emptySeq', function (): void {
            $this->lookupStaffUseCase
                ->expects('handle')
                ->andReturn(Seq::emptySeq());
            $this->lookupShiftUseCase
                ->expects('handle')
                ->andReturn(Seq::from($this->examples->shifts[3]));

            $this->assertThrows(
                NotFoundException::class,
                function (): void {
                    $this->interactor->handle($this->context, $this->examples->shifts[0]->id, $this->getEditValue());
                }
            );
        });
        $this->should('use lookupStaffUseCase when lookupShiftUseCase return confirmed shift', function () {
            $this->lookupShiftUseCase
                ->expects('handle')
                ->with($this->context, Permission::updateShifts(), $this->examples->shifts[1]->id)
                ->andReturn(Seq::from($this->examples->shifts[1]->copy(['isConfirmed' => true])));
            $assignedStaffIds = Seq::fromArray($this->examples->shifts[1]->assignees)
                ->map(fn (Assignee $assignee) => $assignee->staffId)
                ->headOption()
                ->toArray();
            $this->lookupStaffUseCase
                ->expects('handle')
                ->with($this->context, Permission::updateShifts(), ...$assignedStaffIds)
                ->andReturn(Seq::from($this->examples->staffs[15]));

            $this->interactor->handle($this->context, $this->examples->shifts[1]->id, $this->getEditValue());
        });
        $this->should('use LookupUserUseCase when lookupShiftUseCase return confirmed shift', function (): void {
            $this->lookupShiftUseCase
                ->expects('handle')
                ->with($this->context, Permission::updateShifts(), $this->examples->shifts[1]->id)
                ->andReturn(Seq::from($this->examples->shifts[1]->copy(['isConfirmed' => true])));
            $this->lookupUserUseCase
                ->expects('handle')
                ->with($this->context, Permission::updateShifts(), $this->examples->users[0]->id)
                ->andReturn(Seq::from($this->examples->users[0]))
                ->twice();

            $this->interactor->handle($this->context, $this->examples->shifts[1]->id, $this->getEditValue());
        });
        $this->should('use dispatch on EventDispatcher when lookupShiftUseCase return confirmed shift', function () {
            $this->lookupShiftUseCase
                ->expects('handle')
                ->with($this->context, Permission::updateShifts(), $this->examples->shifts[1]->id)
                ->andReturn(Seq::from($this->examples->shifts[1]->copy(['isConfirmed' => true])));
            $this->eventDispatcher
                ->expects('dispatch')
                ->with(equalTo(new UpdateShiftEvent(
                    $this->context,
                    $this->examples->shifts[1],
                    $this->examples->shifts[1]->copy($this->getEditValue()),
                    $this->examples->users[0],
                    $this->examples->users[0],
                    $this->examples->staffs[0]
                )))
                ->andReturnNull();

            $this->interactor->handle($this->context, $this->examples->shifts[1]->id, $this->getEditValue());
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
            'userId' => $this->examples->shifts[0]->userId,
            'officeId' => $this->examples->shifts[0]->officeId,
            'contractId' => $this->examples->shifts[0]->contractId,
            'assignerId' => $this->examples->shifts[0]->contractId,
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
            'date' => $this->examples->shifts[0]->schedule->date,
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
