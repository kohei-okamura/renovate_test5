<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Billing;

use App\Concretes\PermanentDatabaseTransactionManager;
use Domain\Billing\LtcsBillingStatement;
use Infrastructure\Billing\LtcsBillingStatementRepositoryEloquentImpl;
use ScalikePHP\Map;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\DatabaseMixin;
use Tests\Unit\Test;

/**
 * {@link \Infrastructure\ServiceCodeDictionary\LtcsBillingStatementRepositoryEloquentImpl} のテスト.
 */
final class LtcsBillingStatementRepositoryEloquentImplTest extends Test
{
    use CarbonMixin;
    use DatabaseMixin;
    use ExamplesConsumer;
    use UnitSupport;

    private LtcsBillingStatementRepositoryEloquentImpl $repository;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (LtcsBillingStatementRepositoryEloquentImplTest $self): void {
            $self->repository = app(LtcsBillingStatementRepositoryEloquentImpl::class);
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
            $expected = $this->examples->ltcsBillingStatements[0];

            $actual = $this->repository->lookup($expected->id);

            $this->assertEquals(1, $actual->size());
            $this->assertModelStrictEquals(
                $expected,
                $actual->head()
            );
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
    public function describe_store(): void
    {
        $this->should('add the entity to repository when it does not exist in repository', function (): void {
            $entity = $this->examples->ltcsBillingStatements[0]->copy(['id' => 20200925]);

            $actual = $this->repository->store($entity);

            $expected = $this->repository->lookup($actual->id);
            $this->assertCount(1, $expected);
            $this->assertModelStrictEquals($actual, $expected->head());
        });
        $this->should('update the entity', function (): void {
            $entity = $this->examples->ltcsBillingStatements[0]->copy([
                'insurerNumber' => '131011',
                'insurerName' => '杜王町',
            ]);

            $actual = $this->repository->store($entity);

            $expected = $this->repository->lookup($actual->id);
            $this->assertCount(1, $expected);
            $this->assertModelStrictEquals($actual, $expected->head());
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_remove(): void
    {
        $this->should('remove the entity', function (): void {
            $entity = $this->examples->ltcsBillingStatements[0];

            $this->repository->remove($entity);

            $this->assertCount(0, $this->repository->lookup($entity->id));
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_removeById(): void
    {
        $this->should('remove entities', function (): void {
            $entities = [
                $this->examples->ltcsBillingStatements[0],
                $this->examples->ltcsBillingStatements[1],
            ];

            $this->repository->removeById(
                $entities[0]->id,
                $entities[1]->id
            );

            $this->assertCount(0, $this->repository->lookup($entities[0]->id));
            $this->assertCount(0, $this->repository->lookup($entities[1]->id));
        });
        $this->should('not remove other entities', function (): void {
            $entities = [
                $this->examples->ltcsBillingStatements[0],
                $this->examples->ltcsBillingStatements[1],
                $this->examples->ltcsBillingStatements[2],
                $this->examples->ltcsBillingStatements[3],
            ];

            $this->repository->removeById(
                $entities[0]->id,
                $entities[1]->id
            );

            $this->assertCount(1, $this->repository->lookup($entities[2]->id));
            $this->assertCount(1, $this->repository->lookup($entities[3]->id));
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_lookupByBillingId(): void
    {
        $this->should('return Map of Seq with Bundle ID of key', function () {
            $ids = [
                $this->examples->ltcsBillingBundles[0]->id,
                $this->examples->ltcsBillingBundles[1]->id,
            ];
            $actual = $this->repository->lookupByBundleId(...$ids);

            $this->assertInstanceOf(Map::class, $actual);
            $actual->each(function (Seq $x, int $key) use ($ids): void {
                $this->assertTrue(in_array($key, $ids, true));
                $this->assertForAll($x, fn (LtcsBillingStatement $invoice): bool => $invoice->bundleId === $key);
            });
        });
    }
}
