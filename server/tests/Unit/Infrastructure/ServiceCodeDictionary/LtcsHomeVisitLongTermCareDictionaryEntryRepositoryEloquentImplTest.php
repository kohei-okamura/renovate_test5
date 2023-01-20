<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Infrastructure\ServiceCodeDictionary;

use App\Concretes\PermanentDatabaseTransactionManager;
use Domain\ServiceCode\ServiceCode;
use Infrastructure\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionaryEntryRepositoryEloquentImpl;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\DatabaseMixin;
use Tests\Unit\Test;

/**
 * {@link \Infrastructure\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionaryEntryRepositoryEloquentImpl} のテスト.
 */
final class LtcsHomeVisitLongTermCareDictionaryEntryRepositoryEloquentImplTest extends Test
{
    use CarbonMixin;
    use DatabaseMixin;
    use ExamplesConsumer;
    use UnitSupport;

    private LtcsHomeVisitLongTermCareDictionaryEntryRepositoryEloquentImpl $repository;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (LtcsHomeVisitLongTermCareDictionaryEntryRepositoryEloquentImplTest $self): void {
            $self->repository = app(LtcsHomeVisitLongTermCareDictionaryEntryRepositoryEloquentImpl::class);
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
            $expected = $this->examples->ltcsHomeVisitLongTermCareDictionaryEntries[0];

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
            $entity = $this->examples->ltcsHomeVisitLongTermCareDictionaryEntries[0]->copy([
                'id' => 20200925,
                'serviceCode' => ServiceCode::fromString('ZZ9981'),
            ]);

            $actual = $this->repository->store($entity);

            $expected = $this->repository->lookup($actual->id);
            $this->assertCount(1, $expected);
            $this->assertModelStrictEquals($actual, $expected->head());
        });
        $this->should('update the entity', function (): void {
            $entity = $this->examples->ltcsHomeVisitLongTermCareDictionaryEntries[0]->copy(['name' => 'test_name']);

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
            $entity = $this->examples->ltcsHomeVisitLongTermCareDictionaryEntries[0];

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
                $this->examples->ltcsHomeVisitLongTermCareDictionaryEntries[0],
                $this->examples->ltcsHomeVisitLongTermCareDictionaryEntries[1],
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
                $this->examples->ltcsHomeVisitLongTermCareDictionaryEntries[0],
                $this->examples->ltcsHomeVisitLongTermCareDictionaryEntries[1],
                $this->examples->ltcsHomeVisitLongTermCareDictionaryEntries[2],
            ];

            $this->repository->removeById(
                $entities[0]->id,
                $entities[1]->id
            );

            $this->assertCount(1, $this->repository->lookup($entities[2]->id));
        });
    }
}
