<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Infrastructure\ServiceCodeDictionary;

use App\Concretes\PermanentDatabaseTransactionManager;
use Infrastructure\ServiceCodeDictionary\DwsVisitingCareForPwsdDictionaryEntryRepositoryEloquentImpl;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\DatabaseMixin;
use Tests\Unit\Test;

/**
 * {@link Infrastructure\ServiceCodeDictionary\DwsVisitingCareForPwsdDictionaryEntry} Test
 */
class DwsVisitingCareForPwsdDictionaryEntryRepositoryEloquentImplTest extends Test
{
    use DatabaseMixin;
    use ExamplesConsumer;
    use UnitSupport;

    private DwsVisitingCareForPwsdDictionaryEntryRepositoryEloquentImpl $repository;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (DwsVisitingCareForPwsdDictionaryEntryRepositoryEloquentImplTest $self): void {
            $self->repository = app(DwsVisitingCareForPwsdDictionaryEntryRepositoryEloquentImpl::class);
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
            $entry = $this->examples->dwsVisitingCareForPwsdDictionaryEntries[0];
            $expected = $entry;
            $actual = $this->repository->lookup($entry->id);
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
        $this->should('return the entity with id when an entity of not having id stored', function (): void {
            $entity = $this->examples->dwsVisitingCareForPwsdDictionaryEntries[1]->copy(['id' => null]);
            $stored = $this->repository->store($entity);
            $this->assertModelStrictEquals(
                $entity->copy(['id' => $stored->id]),
                $stored
            );
        });
        $this->should('add the entity to repository when it does not exist in repository', function (): void {
            $entity = $this->examples->dwsVisitingCareForPwsdDictionaryEntries[1]->copy(['id' => null]);
            $stored = $this->repository->store($entity);
            $actual = $this->repository->lookup($stored->id);
            $this->assertCount(1, $actual);
            $this->assertModelStrictEquals(
                $stored,
                $actual->head()
            );
        });
        $this->should('update the dictionary', function (): void {
            $entry = $this->examples->dwsVisitingCareForPwsdDictionaryEntries[1];
            $entity = $entry->copy(['name' => 'test_name']);
            $stored = $this->repository->store($entity);
            $actual = $this->repository->lookup($stored->id);
            $this->assertCount(1, $actual);
            $this->assertModelStrictEquals(
                $stored,
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
            $entry0 = $this->examples->dwsVisitingCareForPwsdDictionaryEntries[0];
            $entry1 = $this->examples->dwsVisitingCareForPwsdDictionaryEntries[1];
            $this->repository->removeById($entry0->id, $entry1->id);
            $dwsVisitingCareForPwsdDictionary0 = $this->repository->lookup($entry0->id);
            $this->assertCount(0, $dwsVisitingCareForPwsdDictionary0);
            $dwsVisitingCareForPwsdDictionary1 = $this->repository->lookup($entry1->id);
            $this->assertCount(0, $dwsVisitingCareForPwsdDictionary1);
        });
        $this->should('not remove other entities', function (): void {
            $entry0 = $this->examples->dwsVisitingCareForPwsdDictionaryEntries[0];
            $entry1 = $this->examples->dwsVisitingCareForPwsdDictionaryEntries[1];

            $this->repository->removeById($entry1->id);

            $dwsVisitingCareForPwsdDictionary1 = $this->repository->lookup($entry1->id);
            $this->assertCount(0, $dwsVisitingCareForPwsdDictionary1);
            $dwsVisitingCareForPwsdDictionary0 = $this->repository->lookup($entry0->id);
            $this->assertCount(1, $dwsVisitingCareForPwsdDictionary0);
            $this->assertModelStrictEquals($entry0, $dwsVisitingCareForPwsdDictionary0->head());
        });
    }
}
