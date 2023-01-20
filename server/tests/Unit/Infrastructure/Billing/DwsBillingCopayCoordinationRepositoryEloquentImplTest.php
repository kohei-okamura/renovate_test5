<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Billing;

use App\Concretes\PermanentDatabaseTransactionManager;
use Domain\Billing\DwsBillingCopayCoordination;
use Domain\Billing\DwsBillingCopayCoordinationPayment;
use Infrastructure\Billing\DwsBillingCopayCoordinationRepositoryEloquentImpl;
use ScalikePHP\Map;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\DatabaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * DwsBillingCopayCoordinationRepositoryEloquentImpl のテスト.
 */
class DwsBillingCopayCoordinationRepositoryEloquentImplTest extends Test
{
    use CarbonMixin;
    use DatabaseMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use UnitSupport;

    private DwsBillingCopayCoordinationRepositoryEloquentImpl $repository;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (DwsBillingCopayCoordinationRepositoryEloquentImplTest $self): void {
            $self->repository = app(DwsBillingCopayCoordinationRepositoryEloquentImpl::class);
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
    public function describe_lookupByBundleId(): void
    {
        $this->should('return Map of Seq with bundleId as key', function () {
            $ids = [
                $this->examples->dwsBillingBundles[0]->id,
                $this->examples->dwsBillingBundles[1]->id,
            ];
            $actual = $this->repository->lookupByBundleId(...$ids);

            $this->assertInstanceOf(Map::class, $actual);
            $actual->each(function (Seq $x, int $key) use ($ids): void {
                $this->assertTrue(in_array($key, $ids, true));
                $this->assertForAll($x, fn (DwsBillingCopayCoordination $bundle): bool => $bundle->dwsBillingBundleId === $key);
            });
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_lookup(): void
    {
        $this->should('return an entity when the id exists in db', function (): void {
            $actual = $this->repository->lookup($this->examples->dwsBillingCopayCoordinations[0]->id);
            $this->assertCount(1, $actual);
            $this->assertModelStrictEquals(
                $this->examples->dwsBillingCopayCoordinations[0],
                $actual->head()
            );
        });
        $this->should('return empty seq NotFoundException when the id not exists in db', function (): void {
            $actual = $this->repository->lookup(self::NOT_EXISTING_ID);
            $this->assertCount(0, $actual);
        });
        $this->should('return an entity when DwsBillingCopayCoordinationItem is sorted', function (): void {
            $exampleStatement = $this->examples->dwsBillingCopayCoordinations[3];
            $statement = $exampleStatement->copy(['items' => [
                $exampleStatement->items[1],
                $exampleStatement->items[0],
            ]]);
            $this->repository->store($statement);
            $actual = $this->repository->lookup($this->examples->dwsBillingCopayCoordinations[3]->id);
            $this->assertEquals(1, $actual->head()->items[0]->itemNumber);
            $this->assertEquals(2, $actual->head()->items[1]->itemNumber);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_store()
    {
        $this->should('return the entity with id when an entity of not having id stored', function (): void {
            $attrs = [
                'dwsBillingId' => $this->examples->dwsBillingCopayCoordinations[0]->dwsBillingId,
                'dwsBillingBundleId' => $this->examples->dwsBillingCopayCoordinations[2]->dwsBillingBundleId,
                'office' => $this->examples->dwsBillingCopayCoordinations[0]->office,
                'user' => $this->examples->dwsBillingCopayCoordinations[1]->user,
                'result' => $this->examples->dwsBillingCopayCoordinations[0]->result,
                'exchangeAim' => $this->examples->dwsBillingCopayCoordinations[0]->exchangeAim,
                'items' => $this->examples->dwsBillingCopayCoordinations[0]->items,
                'total' => $this->examples->dwsBillingCopayCoordinations[0]->total,
                'status' => $this->examples->dwsBillingCopayCoordinations[0]->status,
                'createdAt' => $this->examples->dwsBillingCopayCoordinations[0]->createdAt,
                'updatedAt' => $this->examples->dwsBillingCopayCoordinations[0]->updatedAt,
            ];
            $entity = DwsBillingCopayCoordination::create($attrs);

            $stored = $this->repository->store($entity);
            $this->assertModelStrictEquals(
                $entity->copy(['id' => $stored->id]),
                $stored
            );
        });
        $this->should('add the entity to repository when it does not exist in repository', function (): void {
            $attrs = [
                'dwsBillingId' => $this->examples->dwsBillingCopayCoordinations[0]->dwsBillingId,
                'dwsBillingBundleId' => $this->examples->dwsBillingCopayCoordinations[3]->dwsBillingBundleId,
                'office' => $this->examples->dwsBillingCopayCoordinations[0]->office,
                'user' => $this->examples->dwsBillingCopayCoordinations[4]->user,
                'result' => $this->examples->dwsBillingCopayCoordinations[0]->result,
                'exchangeAim' => $this->examples->dwsBillingCopayCoordinations[0]->exchangeAim,
                'items' => $this->examples->dwsBillingCopayCoordinations[0]->items,
                'total' => $this->examples->dwsBillingCopayCoordinations[0]->total,
                'status' => $this->examples->dwsBillingCopayCoordinations[0]->status,
                'createdAt' => $this->examples->dwsBillingCopayCoordinations[0]->createdAt,
                'updatedAt' => $this->examples->dwsBillingCopayCoordinations[0]->updatedAt,
            ];
            $entity = DwsBillingCopayCoordination::create($attrs);

            $stored = $this->repository->store($entity);

            $actual = $this->repository->lookup($stored->id);
            $this->assertCount(1, $actual);
            $this->assertModelStrictEquals(
                $stored,
                $actual->head()
            );
        });
        $this->should('update the entity', function (): void {
            $this->assertNotEquals(500, $this->examples->dwsBillingCopayCoordinations[0]->total->copay);
            $items = $this->examples->dwsBillingCopayCoordinations[0]->items;
            $items[0] = $items[0]->copy([
                'subtotal' => DwsBillingCopayCoordinationPayment::create([
                    'fee' => 0,
                    'copay' => 0,
                    'coordinatedCopay' => 0,
                ]),
            ]);
            $dwsBillingCopayCoordination = $this->examples->dwsBillingCopayCoordinations[0]->copy([
                'total' => DwsBillingCopayCoordinationPayment::create([
                    'fee' => 100,
                    'copay' => 500,
                    'coordinatedCopay' => 100,
                ]),
                'items' => $items,
            ]);
            $this->repository->store($dwsBillingCopayCoordination);
            $actual = $this->repository->lookup($this->examples->dwsBillingCopayCoordinations[0]->id);

            $this->assertCount(1, $actual);
            $this->assertModelStrictEquals(
                $dwsBillingCopayCoordination,
                $actual->head()
            );
        });
        $this->should('return stored entity', function (): void {
            $entity = $this->examples->dwsBillingCopayCoordinations[0]->copy([
                'total' => DwsBillingCopayCoordinationPayment::create([
                    'fee' => 100,
                    'copay' => 500,
                    'coordinatedCopay' => 100,
                ]),
            ]);
            $this->assertNotEquals(500, $this->examples->dwsBillingCopayCoordinations[0]->total->copay);
            $this->assertModelStrictEquals($entity, $this->repository->store($entity));
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_removeById(): void
    {
        $this->should('remove entities', function (): void {
            $this->repository->removeById($this->examples->dwsBillingCopayCoordinations[2]->id, $this->examples->dwsBillingCopayCoordinations[3]->id);

            $dwsBillingCopayCoordinations0 = $this->repository->lookup($this->examples->dwsBillingCopayCoordinations[2]->id);
            $dwsBillingCopayCoordinations1 = $this->repository->lookup($this->examples->dwsBillingCopayCoordinations[3]->id);
            $this->assertCount(0, $dwsBillingCopayCoordinations0);
            $this->assertCount(0, $dwsBillingCopayCoordinations1);
        });
        $this->should('not remove other entities', function (): void {
            $this->repository->removeById($this->examples->dwsBillingCopayCoordinations[2]->id);

            $actual = $this->repository->lookup($this->examples->dwsBillingCopayCoordinations[2]->id);
            $this->assertCount(0, $actual);

            $this->assertTrue($this->repository->lookup($this->examples->dwsBillingCopayCoordinations[1]->id)->nonEmpty());
            $this->assertTrue($this->repository->lookup($this->examples->dwsBillingCopayCoordinations[3]->id)->nonEmpty());
        });
    }
}
