<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Contract;

use App\Concretes\PermanentDatabaseTransactionManager;
use Closure;
use Domain\Contract\Contract;
use Domain\Permission\Permission;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\ContractRepositoryMixin;
use Tests\Unit\Mixins\EnsureUserUseCaseMixin;
use Tests\Unit\Mixins\LoggerMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\TransactionManagerMixin;
use Tests\Unit\Test;
use UseCase\Contract\CreateContractInteractor;

/**
 * CreateContractInteractor のテスト.
 */
class CreateContractInteractorTest extends Test
{
    use CarbonMixin;
    use ContextMixin;
    use EnsureUserUseCaseMixin;
    use ExamplesConsumer;
    use LoggerMixin;
    use MockeryMixin;
    use ContractRepositoryMixin;
    use TransactionManagerMixin;
    use UnitSupport;

    private CreateContractInteractor $interactor;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (CreateContractInteractorTest $self): void {
            $self->contractRepository
                ->allows('store')
                ->andReturn($self->examples->contracts[0])
                ->byDefault();

            $self->contractRepository
                ->allows('transactionManager')
                ->andReturn(PermanentDatabaseTransactionManager::class)
                ->passthru();

            $self->ensureUserUseCase
                ->allows('handle')
                ->andReturnNull()
                ->byDefault();

            $self->logger
                ->allows('info')
                ->byDefault();

            $self->interactor = app(CreateContractInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('store the Contract after transaction begun', function (): void {
            $this->transactionManager
                ->expects('run')
                ->andReturnUsing(function (Closure $callback) {
                    // トランザクションを開始する前に登録処理が行われないことを検証するため
                    // `run` に渡されたコールバック関数を呼び出す直前にモックの定義を行う
                    // なお、トランザクションの終了後に登録処理が行われないことの検証は（恐らく）できない
                    $this->contractRepository
                        ->expects('store')
                        ->withArgs(function (Contract $x) {
                            $this->assertEquals($this->examples->organizations[0]->id, $x->organizationId);
                            $this->assertEquals($this->examples->users[0]->id, $x->userId);
                            return true;
                        })->andReturn($this->examples->contracts[0]);
                    return $callback();
                });

            $this->interactor->handle($this->context, $this->examples->users[0]->id, $this->examples->contracts[0]);
        });
        $this->should('return the Contract', function (): void {
            $this->assertModelStrictEquals(
                $this->examples->contracts[0],
                $this->interactor->handle($this->context, $this->examples->users[0]->id, $this->examples->contracts[0])
            );
        });
        $this->should('log using info', function (): void {
            $context = ['organizationId' => $this->examples->organizations[0]->id];
            $this->context
                ->expects('logContext')
                ->andReturn($context);
            $this->logger
                ->expects('info')
                ->with('契約が登録されました', ['id' => $this->examples->contracts[0]->id] + $context);

            $this->interactor->handle($this->context, $this->examples->users[0]->id, $this->examples->contracts[0]);
        });
        $this->should('use EnsureUserUseCase', function (): void {
            $this->ensureUserUseCase
                ->expects('handle')
                ->with($this->context, Permission::createDwsContracts(), $this->examples->users[0]->id)
                ->andReturnNull();

            $this->interactor->handle($this->context, $this->examples->users[0]->id, $this->examples->contracts[0]);
        });
        $this->should('use EnsureUserUseCase for Ltcs', function (): void {
            $this->ensureUserUseCase
                ->expects('handle')
                ->with($this->context, Permission::createLtcsContracts(), $this->examples->users[0]->id)
                ->andReturnNull();

            $this->interactor->handle($this->context, $this->examples->users[0]->id, $this->examples->contracts[1]);
        });
    }
}
