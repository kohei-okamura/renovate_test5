<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\OwnExpenseProgram;

use App\Concretes\PermanentDatabaseTransactionManager;
use Closure;
use Domain\Common\Carbon;
use Domain\OwnExpenseProgram\OwnExpenseProgram;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\LoggerMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\OwnExpenseProgramRepositoryMixin;
use Tests\Unit\Mixins\TransactionManagerMixin;
use Tests\Unit\Test;
use UseCase\OwnExpenseProgram\CreateOwnExpenseProgramInteractor;

/**
 * {@link \UseCase\OwnExpenseProgram\CreateOwnExpenseProgramInteractor} のテスト.
 */
class CreateOwnExpenseProgramInteractorTest extends Test
{
    use CarbonMixin;
    use ContextMixin;
    use ExamplesConsumer;
    use LoggerMixin;
    use MockeryMixin;
    use OwnExpenseProgramRepositoryMixin;
    use TransactionManagerMixin;
    use UnitSupport;

    private OwnExpenseProgram $ownExpenseProgram;
    private CreateOwnExpenseProgramInteractor $interactor;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (CreateOwnExpenseProgramInteractorTest $self): void {
            $self->ownExpenseProgramRepository
                ->allows('store')
                ->andReturn($self->examples->ownExpensePrograms[0])
                ->byDefault();
            $self->ownExpenseProgramRepository
                ->allows('transactionManager')
                ->andReturn(PermanentDatabaseTransactionManager::class)
                ->passthru();
            $self->logger
                ->allows('info')
                ->byDefault();

            $self->ownExpenseProgram = $self->examples->ownExpensePrograms[0];
            $self->interactor = app(CreateOwnExpenseProgramInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('store the OwnExpenseProgram after transaction begun', function (): void {
            $this->transactionManager
                ->expects('run')
                ->andReturnUsing(function (Closure $callback) {
                    $this->ownExpenseProgramRepository
                        ->expects('store')
                        ->with(equalTo($this->ownExpenseProgram->copy([
                            'organizationId' => $this->context->organization->id,
                            'version' => 1,
                            'createdAt' => Carbon::now(),
                            'updatedAt' => Carbon::now(),
                        ])))
                        ->andReturn($this->ownExpenseProgram);
                    return $callback();
                });

            $this->interactor->handle($this->context, $this->ownExpenseProgram);
        });
        $this->should('return the OwnExpenseProgram', function (): void {
            $this->assertModelStrictEquals(
                $this->ownExpenseProgram,
                $this->interactor->handle($this->context, $this->ownExpenseProgram)
            );
        });
        $this->should('log using info', function (): void {
            $context = ['organizationId' => $this->examples->organizations[0]->id];
            $this->context
                ->expects('logContext')
                ->andReturn($context);
            $this->logger
                ->expects('info')
                ->with('自費サービス情報が登録されました', ['id' => $this->ownExpenseProgram->id] + $context);

            $this->interactor->handle($this->context, $this->ownExpenseProgram);
        });
    }
}
