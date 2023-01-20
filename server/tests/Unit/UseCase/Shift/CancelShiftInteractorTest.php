<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Shift;

use Closure;
use Domain\Permission\Permission;
use Domain\Shift\Assignee;
use Domain\Shift\CancelShiftEvent;
use Domain\Shift\Shift;
use Lib\Exceptions\NotFoundException;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\EventDispatcherMixin;
use Tests\Unit\Mixins\LoggerMixin;
use Tests\Unit\Mixins\LookupShiftUseCaseMixin;
use Tests\Unit\Mixins\LookupStaffUseCaseMixin;
use Tests\Unit\Mixins\LookupUserUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\ShiftRepositoryMixin;
use Tests\Unit\Mixins\TransactionManagerMixin;
use Tests\Unit\Test;
use UseCase\Shift\CancelShiftInteractor;

/**
 * {@link \UseCase\Shift\CancelShiftInteractor} Test.
 */
class CancelShiftInteractorTest extends Test
{
    use ContextMixin;
    use EventDispatcherMixin;
    use ExamplesConsumer;
    use LoggerMixin;
    use LookupShiftUseCaseMixin;
    use LookupStaffUseCaseMixin;
    use LookupUserUseCaseMixin;
    use MockeryMixin;
    use ShiftRepositoryMixin;
    use TransactionManagerMixin;
    use UnitSupport;

    private CancelShiftInteractor $interactor;
    private string $reason = 'キャンセル理由';

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (CancelShiftInteractorTest $self): void {
            $self->eventDispatcher
                ->allows('dispatch')
                ->andReturnNull()
                ->byDefault();
            $self->logger
                ->allows('info')
                ->byDefault();
            $self->lookupShiftUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->shifts[1]))
                ->byDefault();
            $self->lookupStaffUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->staffs[0]))
                ->byDefault();
            $self->lookupUserUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->users[0]))
                ->byDefault();
            $self->shiftRepository
                ->allows('store')
                ->andReturn($self->examples->shifts[1])
                ->byDefault();

            $self->interactor = app(CancelShiftInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('update the Shift after transaction begun', function (): void {
            $this->transactionManager
                ->expects('run')
                ->andReturnUsing(function (Closure $callback) {
                    // トランザクションを開始する前に削除処理が行われないことを検証するため
                    // `run` に渡されたコールバック関数を呼び出す直前にモックの定義を行う
                    // なお、トランザクションの終了後に削除処理が行われないことの検証は（恐らく）できない
                    $this->shiftRepository
                        ->expects('store')
                        ->andReturnUsing(function (Shift $shift): Shift {
                            $this->assertTrue($shift->isCanceled);
                            $this->assertSame($this->reason, $shift->reason);
                            return $shift;
                        });
                    return $callback();
                });

            $this->interactor->handle($this->context, $this->reason, $this->examples->shifts[1]->id);
        });
        $this->should('use LookupShiftUseCase', function (): void {
            $this->lookupShiftUseCase
                ->expects('handle')
                ->with($this->context, Permission::updateShifts(), $this->examples->shifts[1]->id)
                ->andReturn(Seq::from($this->examples->shifts[1]));

            $this->interactor->handle($this->context, $this->reason, $this->examples->shifts[1]->id);
        });
        $this->should('throw NotFoundException when id is canceled', function (): void {
            $shift = $this->examples->shifts[2];
            $this->lookupShiftUseCase
                ->expects('handle')
                ->andReturn(Seq::from($shift->copy(['isCanceled' => true])));

            $this->assertThrows(
                NotFoundException::class,
                function () use ($shift): void {
                    $this->interactor->handle($this->context, $this->reason, $shift->id);
                }
            );
        });
        $this->should('use LookupStaffUseCase', function (): void {
            $this->lookupStaffUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    Permission::updateShifts(),
                    ...Seq::fromArray($this->examples->shifts[1]->assignees)
                        ->map(fn (Assignee $assignee) => $assignee->staffId)
                        ->toArray()
                )
                ->andReturn(Seq::from($this->examples->staffs[0]));

            $this->interactor->handle($this->context, $this->reason, $this->examples->shifts[1]->id);
        });
        $this->should('throw NotFoundException when lookupStaffUseCase return emptySeq', function (): void {
            $this->lookupStaffUseCase
                ->expects('handle')
                ->andReturn(Seq::emptySeq());

            $this->assertThrows(
                NotFoundException::class,
                function (): void {
                    $this->interactor->handle($this->context, $this->reason, $this->examples->shifts[1]->id);
                }
            );
        });
        $this->should('use LookupUserUseCase', function (): void {
            $this->lookupUserUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    Permission::updateShifts(),
                    $this->examples->shifts[1]->userId
                )
                ->andReturn(Seq::from($this->examples->users[0]));

            $this->interactor->handle($this->context, $this->reason, $this->examples->shifts[1]->id);
        });
        $this->should('use dispatch on EventDispatcher', function () {
            $this->eventDispatcher
                ->expects('dispatch')
                ->with(anInstanceOf(CancelShiftEvent::class))
                ->andReturnNull();

            $this->interactor->handle($this->context, $this->reason, $this->examples->shifts[1]->id);
        });
    }
}
