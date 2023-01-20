<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Billing;

use App\Concretes\PermanentDatabaseTransactionManager;
use Domain\Billing\DwsBillingBundle;
use Domain\Billing\DwsBillingServiceDetail;
use Domain\Common\Carbon;
use Domain\ServiceCode\ServiceCode;
use Domain\ServiceCodeDictionary\DwsServiceCodeCategory;
use Infrastructure\Billing\DwsBillingBundleRepositoryEloquentImpl;
use ScalikePHP\Map;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\DatabaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * DwsBillingBundleRepositoryEloquentImpl のテスト.
 */
class DwsBillingBundleRepositoryEloquentImplTest extends Test
{
    use CarbonMixin;
    use DatabaseMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use UnitSupport;

    private DwsBillingBundleRepositoryEloquentImpl $repository;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (DwsBillingBundleRepositoryEloquentImplTest $self): void {
            $self->repository = app(DwsBillingBundleRepositoryEloquentImpl::class);
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
            $actual = $this->repository->lookup($this->examples->dwsBillingBundles[0]->id);
            $this->assertCount(1, $actual);
            $this->assertModelStrictEquals(
                $this->examples->dwsBillingBundles[0],
                $actual->head()
            );
        });
        $this->should('return empty seq NotFoundException when the id not exists in db', function (): void {
            $actual = $this->repository->lookup(self::NOT_EXISTING_ID);
            $this->assertCount(0, $actual);
        });

        $this->should('return an entity when entity has multiple DwsBillingServiceDetails', function (): void {
            $dwsBillingBundle = $this->examples->dwsBillingBundles[0]->copy([
                'details' => [
                    DwsBillingServiceDetail::create([
                        'userId' => $this->examples->users[0]->id,
                        'providedOn' => Carbon::today(),
                        'serviceCode' => ServiceCode::fromString('123456'),
                        'serviceCodeCategory' => DwsServiceCodeCategory::physicalCare(),
                        'unitScore' => 500,
                        'isAddition' => false,
                        'count' => 1,
                        'totalScore' => 500,
                    ]),
                    DwsBillingServiceDetail::create([
                        'userId' => $this->examples->users[1]->id,
                        'providedOn' => Carbon::today(),
                        'serviceCode' => ServiceCode::fromString('123456'),
                        'serviceCodeCategory' => DwsServiceCodeCategory::physicalCare(),
                        'unitScore' => 500,
                        'isAddition' => true,
                        'count' => 2,
                        'totalScore' => 1000,
                    ]),
                ],
            ]);
            $this->repository->store($dwsBillingBundle);
            $actual = $this->repository->lookup($this->examples->dwsBillingBundles[0]->id);
            $this->assertModelStrictEquals(
                $dwsBillingBundle,
                $actual->head()
            );
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
                'dwsBillingId' => $this->examples->dwsBillingBundles[0]->dwsBillingId,
                'providedIn' => $this->examples->dwsBillingBundles[0]->providedIn,
                'cityCode' => $this->examples->dwsBillingBundles[0]->cityCode,
                'cityName' => $this->examples->dwsBillingBundles[0]->cityName,
                'details' => $this->examples->dwsBillingBundles[0]->details,
                'createdAt' => $this->examples->dwsBillingBundles[0]->createdAt,
                'updatedAt' => $this->examples->dwsBillingBundles[0]->updatedAt,
            ];
            $entity = DwsBillingBundle::create($attrs);

            $stored = $this->repository->store($entity);
            $this->assertModelStrictEquals(
                $entity->copy(['id' => $stored->id]),
                $stored
            );
        });
        $this->should('add the entity to repository when it does not exist in repository', function (): void {
            $attrs = [
                'dwsBillingId' => $this->examples->dwsBillingBundles[0]->dwsBillingId,
                'providedIn' => $this->examples->dwsBillingBundles[0]->providedIn,
                'cityCode' => $this->examples->dwsBillingBundles[0]->cityCode,
                'cityName' => $this->examples->dwsBillingBundles[0]->cityName,
                'details' => $this->examples->dwsBillingBundles[0]->details,
                'createdAt' => $this->examples->dwsBillingBundles[0]->createdAt,
                'updatedAt' => $this->examples->dwsBillingBundles[0]->updatedAt,
            ];
            $entity = DwsBillingBundle::create($attrs);

            $stored = $this->repository->store($entity);

            $actual = $this->repository->lookup($stored->id);
            $this->assertCount(1, $actual);
            $this->assertModelStrictEquals(
                $stored,
                $actual->head()
            );
        });
        $this->should('update the entity', function (): void {
            $this->assertNotEquals('999999', $this->examples->dwsBillingBundles[0]->cityCode);
            $dwsBillingBundle = $this->examples->dwsBillingBundles[0]->copy(['cityCode' => '999999']);
            $this->repository->store($dwsBillingBundle);
            $actual = $this->repository->lookup($this->examples->dwsBillingBundles[0]->id);

            $this->assertCount(1, $actual);
            $this->assertModelStrictEquals(
                $dwsBillingBundle,
                $actual->head()
            );
        });
        $this->should('return stored entity', function (): void {
            $entity = $this->examples->dwsBillingBundles[0]->copy(['cityCode' => '999999']);
            $this->assertNotEquals('999999', $this->examples->dwsBillingBundles[0]->cityCode);
            $this->assertModelStrictEquals($entity, $this->repository->store($entity));
        });
        $this->should('delete and insert DwsBillingServiceDetail when update the entity', function (): void {
            $this->assertCount(2, $this->examples->dwsBillingBundles[4]->details);
            $dwsBillingBundle = $this->examples->dwsBillingBundles[4]->copy(['details' => $this->examples->dwsBillingBundles[0]->details]);
            $this->repository->store($dwsBillingBundle);

            /** @var \Domain\Billing\DwsBillingBundle $actual */
            $actual = $this->repository->lookup($dwsBillingBundle->id)->head();
            $this->assertCount(1, $actual->details);
            $this->assertEach(
                function ($a, $b): void {
                    $this->assertModelStrictEquals($a, $b);
                },
                $dwsBillingBundle->details,
                $actual->details,
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
            $this->repository->removeById($this->examples->dwsBillingBundles[2]->id, $this->examples->dwsBillingBundles[3]->id);

            $dwsBillingBundles0 = $this->repository->lookup($this->examples->dwsBillingBundles[2]->id);
            $dwsBillingBundles1 = $this->repository->lookup($this->examples->dwsBillingBundles[3]->id);
            $this->assertCount(0, $dwsBillingBundles0);
            $this->assertCount(0, $dwsBillingBundles1);
        });
        $this->should('not remove other entities', function (): void {
            $this->repository->removeById($this->examples->dwsBillingBundles[2]->id);

            $actual = $this->repository->lookup($this->examples->dwsBillingBundles[2]->id);
            $this->assertCount(0, $actual);

            $this->assertTrue($this->repository->lookup($this->examples->dwsBillingBundles[1]->id)->nonEmpty());
            $this->assertTrue($this->repository->lookup($this->examples->dwsBillingBundles[3]->id)->nonEmpty());
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_lookupByBillingId(): void
    {
        $this->should('return Map of Seq with Billing ID of key', function () {
            $ids = [
                $this->examples->ltcsBillings[0]->id,
                $this->examples->ltcsBillings[1]->id,
            ];
            $actual = $this->repository->lookupByBillingId(...$ids);

            $this->assertInstanceOf(Map::class, $actual);
            $actual->each(function (Seq $x, int $key) use ($ids): void {
                $this->assertTrue(in_array($key, $ids, true));
                $this->assertForAll($x, fn (DwsBillingBundle $bundle): bool => $bundle->dwsBillingId === $key);
            });
        });
    }
}
