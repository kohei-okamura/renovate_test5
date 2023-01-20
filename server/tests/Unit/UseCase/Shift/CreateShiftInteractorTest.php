<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Shift;

use App\Concretes\PermanentDatabaseTransactionManager;
use Closure;
use Domain\Common\Carbon;
use Domain\Permission\Permission;
use Lib\Exceptions\NotFoundException;
use ScalikePHP\Option;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\IdentifyContractUseCaseMixin;
use Tests\Unit\Mixins\LoggerMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\ShiftRepositoryMixin;
use Tests\Unit\Mixins\TransactionManagerMixin;
use Tests\Unit\Test;
use UseCase\Shift\CreateShiftInteractor;

/**
 * CreateShiftInteractor のテスト.
 */
class CreateShiftInteractorTest extends Test
{
    use CarbonMixin;
    use ContextMixin;
    use ExamplesConsumer;
    use IdentifyContractUseCaseMixin;
    use LoggerMixin;
    use MockeryMixin;
    use ShiftRepositoryMixin;
    use TransactionManagerMixin;
    use UnitSupport;

    private CreateShiftInteractor $interactor;

    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (CreateShiftInteractorTest $self): void {
            $self->identifyContractUseCase
                ->allows('handle')
                ->andReturn(Option::from($self->examples->contracts[0]))
                ->byDefault();
            $self->shiftRepository
                ->allows('store')
                ->andReturn($self->examples->shifts[0])
                ->byDefault();
            $self->shiftRepository
                ->allows('transactionManager')
                ->andReturn(PermanentDatabaseTransactionManager::class)
                ->passthru();
            $self->logger
                ->allows('info')
                ->byDefault();

            $self->interactor = app(CreateShiftInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('store the Shift after transaction begun', function (): void {
            $shift = $this->examples->shifts[0];
            $this->transactionManager
                ->expects('run')
                ->andReturnUsing(function (Closure $callback) use ($shift) {
                    // トランザクションを開始する前に登録処理が行われないことを検証するため
                    // `run`に渡されたコールバック関数を呼び出す直前にモックの定義を行う
                    // なお、トランザクションの終了後に登録処理が行われないことの検証は（恐らく）できない
                    $this->shiftRepository->expects('store')->andReturn($shift);
                    return $callback();
                });

            $this->interactor->handle($this->context, $shift);
        });
        $this->should('set organizationId when userId is null', function (): void {
            $shift = $this->examples->shifts[0]->copy(['userId' => null]);
            $this->shiftRepository
                ->expects('store')
                ->with(equalTo($shift->copy([
                    'organization_id' => $this->context->organization->id,
                    'contractId' => null,
                ])))
                ->andReturn($shift);

            $this->interactor->handle($this->context, $shift);
        });
        $this->should('set organizationId and contractId when userId is not null', function (): void {
            $shift = $this->examples->shifts[0];
            $this->shiftRepository
                ->expects('store')
                ->with(equalTo($shift->copy([
                    'organization_id' => $this->context->organization->id,
                    'contractId' => $this->examples->contracts[0]->id,
                ])))
                ->andReturn($shift);

            $this->interactor->handle($this->context, $shift);
        });
        $this->should('use IdentifyContractUseCase when userId is not null', function (): void {
            $shift = $this->examples->shifts[4];
            $this->identifyContractUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    Permission::createShifts(),
                    $shift->officeId,
                    $shift->userId,
                    $shift->task->toServiceSegment()->get(),
                    equalTo(Carbon::now())
                )
                ->andReturn(Option::from($this->examples->contracts[0]));

            $this->interactor->handle($this->context, $shift);
        });
        $this->should('throw NotFoundException when IdentifyContractUseCase return none', function (): void {
            $this->identifyContractUseCase
                ->expects('handle')
                ->andReturn(Option::none());

            $this->assertThrows(NotFoundException::class, function (): void {
                $this->interactor->handle($this->context, $this->examples->shifts[0]);
            });
        });
        $this->should('return the shift', function (): void {
            $this->assertEquals(
                $this->examples->shifts[0],
                $this->interactor->handle($this->context, $this->examples->shifts[0])
            );
        });
        $this->should('log using info', function (): void {
            $context = ['organizationId' => $this->examples->organizations[0]->id];
            $this->context
                ->expects('logContext')
                ->andReturn($context);
            $this->logger
                ->expects('info')
                ->with('勤務シフトが登録されました', ['id' => $this->examples->shifts[0]->id] + $context);

            $this->interactor->handle($this->context, $this->examples->shifts[0]);
        });
    }
}
