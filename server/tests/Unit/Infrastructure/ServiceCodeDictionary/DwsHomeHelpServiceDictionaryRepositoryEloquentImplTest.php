<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Infrastructure\ServiceCodeDictionary;

use App\Concretes\PermanentDatabaseTransactionManager;
use Domain\ServiceCodeDictionary\DwsHomeHelpServiceDictionary;
use Infrastructure\ServiceCodeDictionary\DwsHomeHelpServiceDictionaryRepositoryEloquentImpl;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\DatabaseMixin;
use Tests\Unit\Test;

/**
 * DwsHomeHelpServiceDictionaryRepositoryEloquentImpl のテスト.
 */
final class DwsHomeHelpServiceDictionaryRepositoryEloquentImplTest extends Test
{
    use CarbonMixin;
    use DatabaseMixin;
    use ExamplesConsumer;
    use UnitSupport;

    private DwsHomeHelpServiceDictionary $dwsHomeHelpServiceDictionary;
    private DwsHomeHelpServiceDictionaryRepositoryEloquentImpl $repository;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (DwsHomeHelpServiceDictionaryRepositoryEloquentImplTest $self): void {
            $self->dwsHomeHelpServiceDictionary = $self->examples->dwsHomeHelpServiceDictionaries[0];
            $self->repository = app(DwsHomeHelpServiceDictionaryRepositoryEloquentImpl::class);
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
            $expected = $this->dwsHomeHelpServiceDictionary;
            $actual = $this->repository->lookup($this->dwsHomeHelpServiceDictionary->id);
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
            $entity = $this->dwsHomeHelpServiceDictionary->copy(['id' => 20200925]);
            $stored = $this->repository->store($entity);
            $actual = $this->repository->lookup($stored->id);
            $this->assertCount(1, $actual);
            $this->assertModelStrictEquals(
                $stored,
                $actual->head()
            );
        });
        $this->should('update the dictionary', function (): void {
            $entity = $this->dwsHomeHelpServiceDictionary->copy(['name' => 'test_name']);
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
            $dwsHomeHelpServiceDictionary = $this->dwsHomeHelpServiceDictionary;
            $this->repository->remove($dwsHomeHelpServiceDictionary);
            $actual = $this->repository->lookup($this->dwsHomeHelpServiceDictionary->id);
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
            $this->repository->removeById($this->dwsHomeHelpServiceDictionary->id, $this->examples->dwsHomeHelpServiceDictionaries[1]->id);
            $dwsHomeHelpServiceDictionary0 = $this->repository->lookup($this->dwsHomeHelpServiceDictionary->id);
            $this->assertCount(0, $dwsHomeHelpServiceDictionary0);
            $dwsHomeHelpServiceDictionary1 = $this->repository->lookup($this->examples->dwsHomeHelpServiceDictionaries[1]->id);
            $this->assertCount(0, $dwsHomeHelpServiceDictionary1);
        });
        $this->should('not remove other entities', function (): void {
            $this->repository->removeById($this->dwsHomeHelpServiceDictionary->id);
            $dwsHomeHelpServiceDictionary0 = $this->repository->lookup($this->dwsHomeHelpServiceDictionary->id);
            $this->assertCount(0, $dwsHomeHelpServiceDictionary0);
            $dwsHomeHelpServiceDictionary1 = $this->repository->lookup($this->examples->dwsHomeHelpServiceDictionaries[1]->id);
            $dwsHomeHelpServiceDictionary2 = $this->repository->lookup($this->examples->dwsHomeHelpServiceDictionaries[2]->id);
            $this->assertCount(1, $dwsHomeHelpServiceDictionary1);
            $this->assertModelStrictEquals($this->examples->dwsHomeHelpServiceDictionaries[1], $dwsHomeHelpServiceDictionary1->head());
            $this->assertCount(1, $dwsHomeHelpServiceDictionary2);
            $this->assertModelStrictEquals($this->examples->dwsHomeHelpServiceDictionaries[2], $dwsHomeHelpServiceDictionary2->head());
        });
    }
}
