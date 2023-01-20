<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Office;

use App\Concretes\PermanentDatabaseTransactionManager;
use Domain\Common\Carbon;
use Domain\Office\OfficeGroup as DomainOfficeGroup;
use Infrastructure\Office\OfficeGroupRepositoryEloquentImpl;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\DatabaseMixin;
use Tests\Unit\Test;

/**
 * OfficeGroupRepositoryEloquentImpl のテスト.
 */
class OfficeGroupRepositoryEloquentImplTest extends Test
{
    use DatabaseMixin;
    use ExamplesConsumer;
    use UnitSupport;

    private OfficeGroupRepositoryEloquentImpl $repository;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (OfficeGroupRepositoryEloquentImplTest $self): void {
            $self->repository = app(OfficeGroupRepositoryEloquentImpl::class);
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
            $expected = $this->examples->officeGroups[0];
            $actual = $this->repository->lookup($this->examples->officeGroups[0]->id);
            $this->assertCount(1, $actual);
            $this->assertModelStrictEquals($expected, $actual->head());
        });
        $this->should('return entities when the ids exists in db', function (): void {
            $expected = [$this->examples->officeGroups[0], $this->examples->officeGroups[1]];

            $actual = $this->repository->lookup(
                $this->examples->officeGroups[0]->id,
                $this->examples->officeGroups[1]->id
            );

            $this->assertCount(count($expected), $actual);
            $this->assertEach(
                function ($a, $b): void {
                    $this->assertModelStrictEquals($a, $b);
                },
                $expected,
                $actual->toArray()
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
            $attrs = [
                'id' => self::NOT_EXISTING_ID,
                'organizationId' => $this->examples->organizations[0]->id,
                'parentOfficeGroupId' => $this->examples->officeGroups[1]->id,
                'name' => '関東',
                'sortOrder' => 4,
                'createdAt' => Carbon::create(2019, 1, 1, 1, 1, 1),
                'updatedAt' => Carbon::create(2019, 2, 2, 2, 2, 2),
            ];
            $entity = DomainOfficeGroup::create($attrs);
            $stored = $this->repository->store($entity);
            $actual = $this->repository->lookup($stored->id);
            $this->assertEquals(1, $actual->size());
            $this->assertModelStrictEquals($stored, $actual->head());
        });
        $this->should('update the entity', function (): void {
            $this->assertNotEquals('東海', $this->examples->officeGroups[0]->name);
            $officeGroup = $this->examples->officeGroups[0]->copy(['name' => '東海']);
            $this->repository->store($officeGroup);
            $actual = $this->repository->lookup($this->examples->officeGroups[0]->id);
            $this->assertCount(1, $actual);
            $this->assertModelStrictEquals($officeGroup, $actual->head());
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_remove(): void
    {
        $this->should('remove the entity', function (): void {
            $this->repository->remove($this->examples->officeGroups[2]);
            $actual = $this->repository->lookup($this->examples->officeGroups[2]->id);
            $this->assertCount(0, $actual);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_removeById(): void
    {
        $this->should('remove entities', function (): void {
            $this->repository->removeById($this->examples->officeGroups[2]->id, $this->examples->officeGroups[3]->id);

            $this->assertCount(0, $this->repository->lookup($this->examples->officeGroups[2]->id));
            $this->assertCount(0, $this->repository->lookup($this->examples->officeGroups[3]->id));
        });
        $this->should('not remove other entities', function (): void {
            $this->repository->removeById($this->examples->officeGroups[2]->id);

            $officeGroup0 = $this->repository->lookup($this->examples->officeGroups[0]->id);
            $officeGroup1 = $this->repository->lookup($this->examples->officeGroups[1]->id);
            $this->assertCount(1, $officeGroup0);
            $this->assertModelStrictEquals($this->examples->officeGroups[0], $officeGroup0->head());
            $this->assertCount(1, $officeGroup1);
            $this->assertModelStrictEquals($this->examples->officeGroups[1], $officeGroup1->head());
        });
    }
}
