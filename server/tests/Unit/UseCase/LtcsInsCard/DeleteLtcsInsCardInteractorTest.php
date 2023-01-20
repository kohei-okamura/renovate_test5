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
use Lib\Exceptions\NotFoundException;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\LoggerMixin;
use Tests\Unit\Mixins\LookupLtcsInsCardUseCaseMixin;
use Tests\Unit\Mixins\LtcsInsCardRepositoryMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\TransactionManagerMixin;
use Tests\Unit\Test;
use UseCase\LtcsInsCard\DeleteLtcsInsCardInteractor;

/**
 * DeleteLtcsInsCardInteractor のテスト.
 */
class DeleteLtcsInsCardInteractorTest extends Test
{
    use CarbonMixin;
    use ContextMixin;
    use ExamplesConsumer;
    use LoggerMixin;
    use LookupLtcsInsCardUseCaseMixin;
    use LtcsInsCardRepositoryMixin;
    use MockeryMixin;
    use TransactionManagerMixin;
    use UnitSupport;

    private DeleteLtcsInsCardInteractor $interactor;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (DeleteLtcsInsCardInteractorTest $self): void {
            $self->lookupLtcsInsCardUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->ltcsInsCards[0]))
                ->byDefault();
            $self->ltcsInsCardRepository
                ->allows('removeById')
                ->andReturn($self->examples->ltcsInsCards[0])
                ->byDefault();
            $self->ltcsInsCardRepository
                ->allows('transactionManager')
                ->andReturn(PermanentDatabaseTransactionManager::class)
                ->passthru();
            $self->logger
                ->allows('info')
                ->byDefault();
            $self->interactor = app(DeleteLtcsInsCardInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('throw a NotFoundException when the userId not have LtcsInsCardId', function (): void {
            $this->lookupLtcsInsCardUseCase
                ->expects('handle')
                ->with($this->context, Permission::deleteLtcsInsCards(), self::NOT_EXISTING_ID, $this->examples->ltcsInsCards[0]->id)
                ->andReturn(Seq::emptySeq());

            $this->assertThrows(
                NotFoundException::class,
                function (): void {
                    $this->interactor->handle(
                        $this->context,
                        self::NOT_EXISTING_ID,
                        $this->examples->ltcsInsCards[0]->id
                    );
                }
            );
        });
        $this->should('log using info', function (): void {
            $this->transactionManager
                ->expects('run')
                ->andReturnUsing(function (Closure $callback) {
                    // トランザクションを開始する前に削除処理が行われないことを検証するため
                    // `run` に渡されたコールバック関数を呼び出す直前にモックの定義を行う
                    // なお、トランザクションの終了後に削除処理が行われないことの検証は（恐らく）できない
                    $this->ltcsInsCardRepository
                        ->expects('removeById');
                    return $callback();
                });

            $context = ['organizationId' => $this->examples->organizations[0]->id];
            $this->context
                ->expects('logContext')
                ->andReturn($context);
            $this->logger
                ->expects('info')
                ->with('介護保険被保険者証が削除されました', ['id' => $this->examples->ltcsInsCards[0]->id] + $context);

            $this->interactor->handle(
                $this->context,
                $this->examples->ltcsInsCards[0]->userId,
                $this->examples->ltcsInsCards[0]->id
            );
        });
        $this->should('throw a NotFoundException when the LtcsInsCardId not exists in db', function (): void {
            $this->lookupLtcsInsCardUseCase
                ->expects('handle')
                ->with($this->context, Permission::deleteLtcsInsCards(), $this->examples->ltcsInsCards[0]->userId, self::NOT_EXISTING_ID)
                ->andReturn(Seq::emptySeq());

            $this->assertThrows(
                NotFoundException::class,
                function (): void {
                    $this->interactor->handle(
                        $this->context,
                        $this->examples->ltcsInsCards[0]->userId,
                        self::NOT_EXISTING_ID
                    );
                }
            );
        });
        $this->should('edit the LtcsInsCard after transaction begun', function (): void {
            $this->transactionManager
                ->expects('run')
                ->andReturnUsing(function (Closure $callback) {
                    // トランザクションを開始する前に削除処理が行われないことを検証するため
                    // `run` に渡されたコールバック関数を呼び出す直前にモックの定義を行う
                    // なお、トランザクションの終了後に削除処理が行われないことの検証は（恐らく）できない
                    $this->ltcsInsCardRepository
                        ->expects('removeById')
                        ->andReturn($this->examples->ltcsInsCards[0]);
                    return $callback();
                });

            $this->interactor->handle(
                $this->context,
                $this->examples->ltcsInsCards[0]->userId,
                $this->examples->ltcsInsCards[0]->id
            );
        });
    }
}
