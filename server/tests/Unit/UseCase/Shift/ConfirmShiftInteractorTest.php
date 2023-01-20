<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
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
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ConfirmShiftAsyncValidatorMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\LoggerMixin;
use Tests\Unit\Mixins\LookupShiftUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\ShiftRepositoryMixin;
use Tests\Unit\Mixins\TransactionManagerMixin;
use Tests\Unit\Test;
use UseCase\Shift\ConfirmShiftInteractor;

/**
 * ConfirmShiftInteractor のテスト.
 */
class ConfirmShiftInteractorTest extends Test
{
    use ConfirmShiftAsyncValidatorMixin;
    use CarbonMixin;
    use ContextMixin;
    use ExamplesConsumer;
    use LoggerMixin;
    use MockeryMixin;
    use ShiftRepositoryMixin;
    use LookupShiftUseCaseMixin;
    use TransactionManagerMixin;
    use UnitSupport;

    private ConfirmShiftInteractor $interactor;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (ConfirmShiftInteractorTest $self): void {
            $self->interactor = app(ConfirmShiftInteractor::class);
            $self->confirmShiftAsyncValidator
                ->allows('validate')
                ->andReturnNull()
                ->byDefault();
            $self->lookupShiftUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->shifts[0]))
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
                ->andReturnNull()
                ->byDefault();
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('use validate on AsyncValidator', function () {
            $this->confirmShiftAsyncValidator
                ->expects('validate')
                ->with(
                    $this->context,
                    ['ids' => [$this->examples->shifts[0]->id]],
                )
                ->andReturnNull();
            $this->interactor->handle($this->context, $this->examples->shifts[0]->id);
        });
        $this->should('use LookupShiftUseCase after transaction begun', function (): void {
            $this->transactionManager
                ->expects('run')
                ->andReturnUsing(function (Closure $callback) {
                    $this->lookupShiftUseCase
                        ->expects('handle')
                        ->with($this->context, Permission::updateShifts(), $this->examples->shifts[0]->id)
                        ->andReturn(Seq::from($this->examples->shifts[0]));
                    return $callback();
                });
            $this->interactor->handle($this->context, $this->examples->shifts[0]->id);
        });
        $this->should('confirm the Shift after transaction begun', function (): void {
            $this->transactionManager
                ->expects('run')
                ->andReturnUsing(function (Closure $callback) {
                    $this->shiftRepository
                        ->expects('store')
                        ->with(equalTo($this->examples->shifts[0]->copy(['isConfirmed' => true])))
                        ->andReturn($this->examples->shifts[0]);
                    return $callback();
                });
            $this->interactor->handle($this->context, $this->examples->shifts[0]->id);
        });
        $this->should('throw a NotFoundException when the id not exists in db', function (): void {
            $this->lookupShiftUseCase
                ->expects('handle')
                ->with($this->context, Permission::updateShifts(), self::NOT_EXISTING_ID)
                ->andReturn(Seq::emptySeq());

            $this->assertThrows(
                NotFoundException::class,
                function (): void {
                    $this->interactor->handle($this->context, self::NOT_EXISTING_ID);
                }
            );
        });
        $this->should('log using info', function (): void {
            $context = ['organizationId' => $this->examples->organizations[0]->id];
            $this->context
                ->expects('logContext')
                ->andReturn($context);
            $this->logger
                ->expects('info')
                ->with('勤務シフトが確定されました', ['id' => ''] + $context)
                ->andReturnNull();

            $this->interactor->handle($this->context, $this->examples->shifts[0]->id);
        });
    }
}
