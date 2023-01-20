<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Infrastructure\ServiceCodeDictionary;

use App\Concretes\PermanentDatabaseTransactionManager;
use Domain\ServiceCodeDictionary\DwsVisitingCareForPwsdDictionary;
use Infrastructure\ServiceCodeDictionary\DwsVisitingCareForPwsdDictionaryRepositoryEloquentImpl;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\DatabaseMixin;
use Tests\Unit\Test;

/**
 * DwsVisitingCareForPwsdDictionaryRepositoryEloquentImpl のテスト.
 */
final class DwsVisitingCareForPwsdDictionaryRepositoryEloquentImplTest extends Test
{
    use CarbonMixin;
    use DatabaseMixin;
    use ExamplesConsumer;
    use UnitSupport;

    private DwsVisitingCareForPwsdDictionary $dwsVisitingCareForPwsdDictionary;
    private DwsVisitingCareForPwsdDictionaryRepositoryEloquentImpl $repository;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (DwsVisitingCareForPwsdDictionaryRepositoryEloquentImplTest $self): void {
            $self->dwsVisitingCareForPwsdDictionary = $self->examples->dwsVisitingCareForPwsdDictionaries[0];
            $self->repository = app(DwsVisitingCareForPwsdDictionaryRepositoryEloquentImpl::class);
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
            $expected = $this->dwsVisitingCareForPwsdDictionary;
            $actual = $this->repository->lookup($this->dwsVisitingCareForPwsdDictionary->id);
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
            $entity = $this->dwsVisitingCareForPwsdDictionary->copy(['id' => 20200925]);
            $stored = $this->repository->store($entity);
            $actual = $this->repository->lookup($stored->id);
            $this->assertCount(1, $actual);
            $this->assertModelStrictEquals(
                $stored,
                $actual->head()
            );
        });
        $this->should('update the dictionary', function (): void {
            $entity = $this->dwsVisitingCareForPwsdDictionary->copy(['name' => 'test_name']);
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
    public function describe_remove(): void
    {
        $this->should('remove the entity', function (): void {
            $dwsVisitingCareForPwsdDictionary = $this->dwsVisitingCareForPwsdDictionary;
            $this->repository->remove($dwsVisitingCareForPwsdDictionary);
            $actual = $this->repository->lookup($this->dwsVisitingCareForPwsdDictionary->id);
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
            $this->repository->removeById($this->dwsVisitingCareForPwsdDictionary->id, $this->examples->dwsVisitingCareForPwsdDictionaries[1]->id);
            $dwsVisitingCareForPwsdDictionary0 = $this->repository->lookup($this->dwsVisitingCareForPwsdDictionary->id);
            $this->assertCount(0, $dwsVisitingCareForPwsdDictionary0);
            $dwsVisitingCareForPwsdDictionary1 = $this->repository->lookup($this->examples->dwsVisitingCareForPwsdDictionaries[1]->id);
            $this->assertCount(0, $dwsVisitingCareForPwsdDictionary1);
        });
        $this->should('not remove other entities', function (): void {
            $this->repository->removeById($this->dwsVisitingCareForPwsdDictionary->id);
            $dwsVisitingCareForPwsdDictionary0 = $this->repository->lookup($this->dwsVisitingCareForPwsdDictionary->id);
            $this->assertCount(0, $dwsVisitingCareForPwsdDictionary0);
            $dwsVisitingCareForPwsdDictionary1 = $this->repository->lookup($this->examples->dwsVisitingCareForPwsdDictionaries[1]->id);
            $dwsVisitingCareForPwsdDictionary2 = $this->repository->lookup($this->examples->dwsVisitingCareForPwsdDictionaries[2]->id);
            $this->assertCount(1, $dwsVisitingCareForPwsdDictionary1);
            $this->assertModelStrictEquals($this->examples->dwsVisitingCareForPwsdDictionaries[1], $dwsVisitingCareForPwsdDictionary1->head());
            $this->assertCount(1, $dwsVisitingCareForPwsdDictionary2);
            $this->assertModelStrictEquals($this->examples->dwsVisitingCareForPwsdDictionaries[2], $dwsVisitingCareForPwsdDictionary2->head());
        });
    }
}
