<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Organization;

use App\Concretes\PermanentDatabaseTransactionManager;
use Domain\Organization\OrganizationSetting;
use Infrastructure\Organization\OrganizationSettingRepositoryEloquentImpl;
use ScalikePHP\Map;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\DatabaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * {@link OrganizationSettingRepositoryEloquentImpl} のテスト.
 */
class OrganizationSettingRepositoryEloquentImplTest extends Test
{
    use CarbonMixin;
    use DatabaseMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use UnitSupport;

    private OrganizationSettingRepositoryEloquentImpl $repository;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (OrganizationSettingRepositoryEloquentImplTest $self): void {
            $self->repository = app(OrganizationSettingRepositoryEloquentImpl::class);
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
            $expected = $this->examples->organizationSettings[0];
            $actual = $this->repository->lookup($this->examples->organizationSettings[0]->id);

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
            $entity = $this->examples->organizationSettings[0]->copy(['id' => self::NOT_EXISTING_ID]);
            $stored = $this->repository->store($entity);
            $actual = $this->repository->lookup($stored->id);
            $this->assertCount(1, $actual);
            $this->assertModelStrictEquals(
                $entity,
                $actual->head()
            );
        });
        $this->should('update the entity', function (): void {
            $newBankingClientCode = '9999999999';
            $this->assertNotEquals($newBankingClientCode, $this->examples->organizationSettings[0]->bankingClientCode);
            $organizationSetting = $this->examples->organizationSettings[0]->copy(['bankingClientCode' => $newBankingClientCode]);
            $this->repository->store($organizationSetting);
            $actual = $this->repository->lookup($this->examples->organizationSettings[0]->id);

            $this->assertCount(1, $actual);
            $this->assertModelStrictEquals(
                $organizationSetting,
                $actual->head()
            );
        });
        $this->should('return stored entity', function (): void {
            $entity = $this->examples->organizationSettings[0]->copy(['id' => self::NOT_EXISTING_ID]);

            $stored = $this->repository->store($entity);
            $this->assertModelStrictEquals(
                $entity,
                $stored
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
            $this->repository->removeById($this->examples->organizationSettings[0]->id, $this->examples->organizationSettings[1]->id);
            $report0 = $this->repository->lookup($this->examples->organizationSettings[0]->id);
            $this->assertCount(0, $report0);
            $report1 = $this->repository->lookup($this->examples->organizationSettings[1]->id);
            $this->assertCount(0, $report1);
        });
        $this->should('not remove other entities', function (): void {
            $this->repository->removeById($this->examples->organizationSettings[0]->id);
            $report0 = $this->repository->lookup($this->examples->organizationSettings[0]->id);
            $this->assertCount(0, $report0);
            $report1 = $this->repository->lookup($this->examples->organizationSettings[1]->id);
            $this->assertCount(1, $report1);
            $this->assertModelStrictEquals($this->examples->organizationSettings[1], $report1->head());
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_lookupByOrganizationId(): void
    {
        $this->should('return Map of Seq with Organization ID of key', function (): void {
            $ids = [
                $this->examples->organizationSettings[0]->id,
                $this->examples->organizationSettings[1]->id,
            ];
            $actual = $this->repository->lookupByOrganizationId(...$ids);

            $this->assertInstanceOf(Map::class, $actual);
            $actual->each(function (Seq $x, int $key) use ($ids): void {
                $this->assertTrue(in_array($key, $ids, true));
                $this->assertForAll($x, fn (OrganizationSetting $organizationSetting): bool => $organizationSetting->organizationId === $key);
            });
        });
    }
}
