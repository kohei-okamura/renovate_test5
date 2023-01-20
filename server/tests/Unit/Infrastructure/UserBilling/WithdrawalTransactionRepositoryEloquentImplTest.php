<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Infrastructure\UserBilling;

use App\Concretes\PermanentDatabaseTransactionManager;
use Domain\UserBilling\WithdrawalTransaction;
use Infrastructure\UserBilling\WithdrawalTransactionRepositoryEloquentImpl;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\DatabaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * {@link \Infrastructure\UserBilling\WithdrawalTransactionRepositoryEloquentImpl} のテスト.
 */
final class WithdrawalTransactionRepositoryEloquentImplTest extends Test
{
    use CarbonMixin;
    use DatabaseMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use UnitSupport;

    private WithdrawalTransactionRepositoryEloquentImpl $repository;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->repository = app(WithdrawalTransactionRepositoryEloquentImpl::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_transactionManager(): void
    {
        $this->should('return a class name of DatabaseTransactionManager', function (): void {
            $this->assertSame(PermanentDatabaseTransactionManager::class, $this->repository->transactionManager());
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_lookup(): void
    {
        $this->should('return an entity when the id exists in db', function (): void {
            $expected = $this->examples->withdrawalTransactions[0];
            $actual = $this->repository->lookup($this->examples->withdrawalTransactions[0]->id);

            $this->assertEquals(1, $actual->size());
            $this->assertModelStrictEquals($expected, $actual->head());
        });
        $this->should('return empty seq when the id not exists in db', function (): void {
            $actual = $this->repository->lookup(self::NOT_EXISTING_ID);
            $this->assertCount(0, $actual);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_store()
    {
        $this->should('add the entity', function (): void {
            $entity = $this->examples->withdrawalTransactions[0]->copy(['id' => null]);
            $stored = $this->repository->store($entity);
            $actual = $this->repository->lookup($stored->id);
            $this->assertCount(1, $actual);
            $this->assertForAll(
                $this->examples->withdrawalTransactions,
                fn (WithdrawalTransaction $x): bool => $x->id !== $stored->id
            );
            $this->assertModelStrictEquals(
                $entity->copy(['id' => $stored->id]),
                $actual->head()
            );
        });
        $this->should('update the entity', function (): void {
            $newDownloadedAt = $this->examples->withdrawalTransactions[0]->downloadedAt->addMonth();
            $this->assertNotEquals($newDownloadedAt, $this->examples->withdrawalTransactions[0]->downloadedAt);

            $withdrawalTransaction = $this->examples->withdrawalTransactions[0]->copy([
                'downloadedAt' => $newDownloadedAt,
            ]);
            $this->repository->store($withdrawalTransaction);
            $actual = $this->repository->lookup($this->examples->withdrawalTransactions[0]->id);

            $this->assertCount(1, $actual);
            $this->assertModelStrictEquals(
                $withdrawalTransaction,
                $actual->head()
            );
        });
        $this->should('return stored entity', function (): void {
            $entity = $this->examples->withdrawalTransactions[0]->copy(['id' => self::NOT_EXISTING_ID]);

            $stored = $this->repository->store($entity);
            $this->assertModelStrictEquals(
                $entity,
                $stored
            );
        });
        $this->should('store when downloadedAt is null', function (): void {
            $entity = $this->examples->withdrawalTransactions[0]->copy([
                'id' => self::NOT_EXISTING_ID,
                'downloadedAt' => null,
            ]);

            $stored = $this->repository->store($entity);
            $actual = $this->repository->lookup($stored->id);
            $this->assertModelStrictEquals(
                $entity->copy(['id' => $stored->id]),
                $actual->head()
            );
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_removeById(): void
    {
        $this->should('remove entities', function (): void {
            $this->repository->removeById($this->examples->withdrawalTransactions[0]->id, $this->examples->withdrawalTransactions[1]->id);
            $withdrawalTransaction0 = $this->repository->lookup($this->examples->withdrawalTransactions[0]->id);
            $this->assertCount(0, $withdrawalTransaction0);
            $withdrawalTransaction1 = $this->repository->lookup($this->examples->withdrawalTransactions[1]->id);
            $this->assertCount(0, $withdrawalTransaction1);
        });
        $this->should('not remove other entities', function (): void {
            $this->repository->removeById($this->examples->withdrawalTransactions[0]->id);
            $withdrawalTransaction0 = $this->repository->lookup($this->examples->withdrawalTransactions[0]->id);
            $this->assertCount(0, $withdrawalTransaction0);
            $withdrawalTransaction1 = $this->repository->lookup($this->examples->withdrawalTransactions[1]->id);
            $this->assertCount(1, $withdrawalTransaction1);
            $this->assertModelStrictEquals($this->examples->withdrawalTransactions[1], $withdrawalTransaction1->head());
        });
    }
}
