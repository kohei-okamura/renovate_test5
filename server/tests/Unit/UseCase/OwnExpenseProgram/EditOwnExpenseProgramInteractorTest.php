<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\OwnExpenseProgram;

use App\Concretes\PermanentDatabaseTransactionManager;
use Closure;
use Domain\OwnExpenseProgram\OwnExpenseProgram;
use Domain\Permission\Permission;
use Lib\Exceptions\NotFoundException;
use Lib\Json;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\LoggerMixin;
use Tests\Unit\Mixins\LookupOwnExpenseProgramUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\OrganizationRepositoryMixin;
use Tests\Unit\Mixins\OwnExpenseProgramRepositoryMixin;
use Tests\Unit\Mixins\TransactionManagerMixin;
use Tests\Unit\Test;
use UseCase\OwnExpenseProgram\EditOwnExpenseProgramInteractor;

class EditOwnExpenseProgramInteractorTest extends Test
{
    use CarbonMixin;
    use ContextMixin;
    use ExamplesConsumer;
    use LoggerMixin;
    use LookupOwnExpenseProgramUseCaseMixin;
    use MockeryMixin;
    use OwnExpenseProgramRepositoryMixin;
    use OrganizationRepositoryMixin;
    use TransactionManagerMixin;
    use UnitSupport;

    private OwnExpenseProgram $ownExpenseProgram;
    private EditOwnExpenseProgramInteractor $interactor;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (EditOwnExpenseProgramInteractorTest $self): void {
            $self->lookupOwnExpenseProgramUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->ownExpensePrograms[0]))
                ->byDefault();
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
            $self->interactor = app(EditOwnExpenseProgramInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('edit the OwnExpenseProgram after transaction begun', function (): void {
            $this->transactionManager
                ->expects('run')
                ->andReturnUsing(function (Closure $callback) {
                    $this->lookupOwnExpenseProgramUseCase
                        ->expects('handle')
                        ->with($this->context, Permission::updateOwnExpensePrograms(), $this->ownExpenseProgram->id)
                        ->andReturn(Seq::from($this->ownExpenseProgram));
                    $this->ownExpenseProgramRepository
                        ->expects('store')
                        ->andReturn($this->ownExpenseProgram);
                    return $callback();
                });

            $this->interactor->handle($this->context, $this->ownExpenseProgram->id, $this->payload());
        });
        $this->should('return the OwnExpenseProgram', function (): void {
            $this->assertModelStrictEquals(
                $this->ownExpenseProgram,
                $this->interactor->handle($this->context, $this->ownExpenseProgram->id, $this->payload())
            );
        });
        $this->should('throw a NotFoundException when LookupOwnExpenseProgramUseCase return empty seq', function (): void {
            $this->lookupOwnExpenseProgramUseCase
                ->expects('handle')
                ->with($this->context, Permission::updateOwnExpensePrograms(), $this->ownExpenseProgram->id)
                ->andReturn(Seq::emptySeq());

            $this->assertThrows(
                NotFoundException::class,
                function (): void {
                    $this->interactor->handle(
                        $this->context,
                        $this->ownExpenseProgram->id,
                        $this->payload()
                    );
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
                ->with('自費サービス情報が更新されました', ['id' => $this->ownExpenseProgram->id] + $context);

            $this->interactor->handle(
                $this->context,
                $this->ownExpenseProgram->id,
                $this->payload()
            );
        });
    }

    /**
     * payload が返す配列.
     *
     * @return array
     */
    private function payload(): array
    {
        return Json::decode(Json::encode($this->examples->ownExpensePrograms[0]), true);
    }
}
