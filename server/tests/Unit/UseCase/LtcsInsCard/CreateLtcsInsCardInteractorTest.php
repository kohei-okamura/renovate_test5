<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\LtcsInsCard;

use App\Concretes\PermanentDatabaseTransactionManager;
use Closure;
use Domain\Permission\Permission;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\EnsureUserUseCaseMixin;
use Tests\Unit\Mixins\LoggerMixin;
use Tests\Unit\Mixins\LtcsInsCardRepositoryMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\TransactionManagerMixin;
use Tests\Unit\Test;
use UseCase\LtcsInsCard\CreateLtcsInsCardInteractor;

/**
 * CreateLtcsInsCardInteractor のテスト.
 */
final class CreateLtcsInsCardInteractorTest extends Test
{
    use CarbonMixin;
    use ContextMixin;
    use EnsureUserUseCaseMixin;
    use ExamplesConsumer;
    use LoggerMixin;
    use LtcsInsCardRepositoryMixin;
    use MockeryMixin;
    use TransactionManagerMixin;
    use UnitSupport;

    private CreateLtcsInsCardInteractor $interactor;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (CreateLtcsInsCardInteractorTest $self): void {
            $self->ensureUserUseCase
                ->allows('handle')
                ->andReturnNull()
                ->byDefault();
            $self->ltcsInsCardRepository
                ->allows('store')
                ->andReturn($self->examples->ltcsInsCards[0])
                ->byDefault();
            $self->ltcsInsCardRepository
                ->allows('transactionManager')
                ->andReturn(PermanentDatabaseTransactionManager::class)
                ->passthru();
            $self->logger
                ->allows('info')
                ->byDefault();

            $self->interactor = app(CreateLtcsInsCardInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('use EnsureUserUseCase', function (): void {
            $this->ensureUserUseCase
                ->expects('handle')
                ->with($this->context, Permission::createLtcsInsCards(), $this->examples->users[0]->id)
                ->andReturnNull();

            $this->interactor->handle($this->context, $this->examples->users[0]->id, $this->examples->ltcsInsCards[0]);
        });
        $this->should('store the LtcsInsCard after transaction begun', function (): void {
            $this->transactionManager
                ->expects('run')
                ->andReturnUsing(function (Closure $callback) {
                    // トランザクションを開始する前に登録処理が行われないことを検証するため
                    // `run` に渡されたコールバック関数を呼び出す直前にモックの定義を行う
                    // なお、トランザクションの終了後に登録処理が行われないことの検証は（恐らく）できない
                    $this->ltcsInsCardRepository->expects('store')->andReturn($this->examples->ltcsInsCards[0]);
                    return $callback();
                });

            $this->interactor->handle($this->context, $this->examples->users[0]->id, $this->examples->ltcsInsCards[0]);
        });
        $this->should('return the LtcsInsCard', function (): void {
            $this->assertModelStrictEquals(
                $this->examples->ltcsInsCards[0],
                $this->interactor->handle(
                    $this->context,
                    $this->examples->users[0]->id,
                    $this->examples->ltcsInsCards[0]
                )
            );
        });
        $this->should('log using info', function (): void {
            $context = ['organizationId' => $this->examples->organizations[0]->id];
            $this->context
                ->expects('logContext')
                ->andReturn($context);
            $this->logger
                ->expects('info')
                ->with('介護保険被保険者証が登録されました', ['id' => $this->examples->ltcsInsCards[0]->id] + $context);

            $this->interactor->handle($this->context, $this->examples->users[0]->id, $this->examples->ltcsInsCards[0]);
        });
    }
}
