<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Organization;

use App\Concretes\PermanentDatabaseTransactionManager;
use Closure;
use Domain\Common\Carbon;
use Infrastructure\Organization\OrganizationRepositoryCacheImpl;
use Lib\Exceptions\NotFoundException;
use ScalikePHP\None;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use ScalikePHP\Some;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CacheMixin;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\DatabaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\OrganizationRepositoryFallbackMixin;
use Tests\Unit\Test;

/**
 * OrganizationRepositoryCacheImpl のテスト.
 */
class OrganizationRepositoryCacheImplTest extends Test
{
    use CacheMixin;
    use CarbonMixin;
    use DatabaseMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use OrganizationRepositoryFallbackMixin;
    use UnitSupport;

    public const NOT_EXISTING_ORGANIZATION_CODE = 'nobody';

    private OrganizationRepositoryCacheImpl $repository;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (OrganizationRepositoryCacheImplTest $self): void {
            $self->organizationRepositoryFallback->allows('lookup')->andReturn(Seq::from($self->examples->organizations[0]))->byDefault();
            $self->repository = app(OrganizationRepositoryCacheImpl::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_transactionManager(): void
    {
        $this->should('return the value using fallback repository', function (): void {
            $this->organizationRepositoryFallback
                ->expects('transactionManager')
                ->andReturn(PermanentDatabaseTransactionManager::class);

            $this->assertSame(PermanentDatabaseTransactionManager::class, $this->repository->transactionManager());
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_lookup(): void
    {
        $this->should('return an entity from cache', function (): void {
            $expected = $this->examples->organizations[0];
            $this->cache->expects('remember')->andReturn(Seq::from($expected));

            $actual = $this->repository->lookup($expected->id);

            $this->assertInstanceOf(Seq::class, $actual);
            $this->assertEquals(1, $actual->size());
            $this->assertModelStrictEquals($expected, $actual->head());
        });
        $this->should('return entities from cache', function (): void {
            $expected = Seq::from($this->examples->organizations[0]);
            $this->cache
                ->expects('remember')
                ->andReturn($expected)
                ->times(2);

            $actual = $this->repository->lookup($this->examples->organizations[0]->id, $this->examples->organizations[1]->id);

            $this->assertInstanceOf(Seq::class, $actual);
            $this->assertCount(2, $actual);
            $this->assertArrayStrictEquals($expected->append($expected)->toArray(), $actual->toArray());
        });
        $this->should('return an entity from fallback when cache does not exist', function (): void {
            $expected = $this->examples->organizations[0];
            $this->cache->expects('remember')->andReturnUsing(fn ($key, $ttl, Closure $callback) => $callback());
            $this->organizationRepositoryFallback
                ->expects('lookup')
                ->with($expected->id)
                ->andReturn(Seq::from($expected));

            $actual = $this->repository->lookup($expected->id);

            $this->assertInstanceOf(Seq::class, $actual);
            $this->assertEquals(1, $actual->size());
            $this->assertModelStrictEquals($expected, $actual->head());
        });
        $this->should('cache an entity from fallback when cache does not exist', function (): void {
            $entity = $this->examples->organizations[0];
            $this->cache
                ->expects('remember')
                ->with(
                    "organization:id:{$entity->id}",
                    equalTo(Carbon::tomorrow()),
                    anInstanceOf(Closure::class)
                )
                ->andReturnUsing(fn ($key, $ttl, Closure $callback) => $callback());

            $this->repository->lookup($entity->id);
        });
        $this->should('throw a NotFoundException when the organization not found', function (): void {
            $this->cache->allows('remember')->andReturnUsing(fn ($key, $ttl, Closure $callback) => $callback());
            $this->organizationRepositoryFallback->allows('lookup')->andThrow(NotFoundException::class);

            $this->assertThrows(
                NotFoundException::class,
                function (): void {
                    $this->repository->lookup(self::NOT_EXISTING_ID);
                }
            );
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_store(): void
    {
        $this->should('store the entity', function (): void {
            $entity = $this->examples->organizations[0];
            $this->cache->allows('add');
            $this->cache->allows('forget');
            $this->organizationRepositoryFallback->expects('store')->with($entity)->andReturn($entity);

            $this->repository->store($entity);
        });
        $this->should('return stored entity', function (): void {
            $entity = $this->examples->organizations[0];
            $expected = $entity->copy(['id' => self::NOT_EXISTING_ID]);
            $this->cache->allows('add');
            $this->cache->allows('forget');
            $this->organizationRepositoryFallback->allows('store')->andReturn($expected);

            $this->assertModelStrictEquals(
                $expected,
                $this->repository->store($entity)
            );
        });
        $this->should('forget cache', function (): void {
            $entity = $this->examples->organizations[0];
            $this->cache->expects('forget')->with("organization:id:{$entity->id}");
            $this->organizationRepositoryFallback->allows('store')->andReturn($entity);

            $this->repository->store($entity);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_remove(): void
    {
        $this->should('remove given entity', function (): void {
            $organization = $this->examples->organizations[0];
            $this->cache->allows('forget');
            $this->organizationRepositoryFallback->expects('remove')->with($organization);

            $this->repository->remove($organization);
        });
        $this->should('forget cache', function (): void {
            $organization = $this->examples->organizations[0];
            $this->cache->expects('forget')->with("organization:id:{$organization->id}");
            $this->organizationRepositoryFallback->allows('remove');

            $this->repository->remove($organization);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_removeById(): void
    {
        $this->should('remove entities', function (): void {
            $this->cache->allows('forget');
            $this->organizationRepositoryFallback->expects('removeById')->with(
                $this->examples->organizations[0]->id,
                $this->examples->organizations[1]->id
            );

            $this->repository->removeById($this->examples->organizations[0]->id, $this->examples->organizations[1]->id);
        });
        $this->should('forget caches', function (): void {
            $this->cache->expects('forget')->with("organization:id:{$this->examples->organizations[0]->id}");
            $this->cache->expects('forget')->with("organization:id:{$this->examples->organizations[1]->id}");
            $this->organizationRepositoryFallback->allows('removeById');

            $this->repository->removeById($this->examples->organizations[0]->id, $this->examples->organizations[1]->id);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_lookupOptionByCode(): void
    {
        $this->should('return a some of entity from cache', function (): void {
            $expected = $this->examples->organizations[0];
            $this->cache->expects('remember')->andReturn(Option::from($expected));

            $option = $this->repository->lookupOptionByCode($expected->code);

            $this->assertInstanceOf(Some::class, $option);
            $this->assertModelStrictEquals(
                $expected,
                $option->get()
            );
        });
        $this->should('return a some of entity from fallback when cache does not exist', function (): void {
            $expected = $this->examples->organizations[0];
            $this->cache->expects('remember')->andReturnUsing(fn ($key, $ttl, Closure $callback) => $callback());
            $this->organizationRepositoryFallback
                ->expects('lookupOptionByCode')
                ->with($expected->code)
                ->andReturn(Option::from($expected));

            $option = $this->repository->lookupOptionByCode($expected->code);

            $this->assertInstanceOf(Some::class, $option);
            $this->assertSame($expected, $option->get());
        });
        $this->should('cache an entity from fallback when cache does not exist', function (): void {
            $entity = $this->examples->organizations[0];
            $this->cache
                ->expects('remember')
                ->with(
                    "organization:code:{$entity->code}",
                    equalTo(Carbon::tomorrow()),
                    anInstanceOf(Closure::class)
                )
                ->andReturnUsing(fn ($key, $ttl, Closure $callback) => $callback());
            $this->organizationRepositoryFallback
                ->expects('lookupOptionByCode')
                ->with($entity->code)
                ->andReturn(Option::from($entity));

            $this->repository->lookupOptionByCode($entity->code);
        });
        $this->should('return none when the entity not exists', function (): void {
            $this->cache->allows('remember')->andReturn(Option::none());

            $this->assertInstanceOf(
                None::class,
                $this->repository->lookupOptionByCode(self::NOT_EXISTING_ORGANIZATION_CODE)
            );
        });
    }
}
