<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Permission;

use App\Concretes\PermanentDatabaseTransactionManager;
use Closure;
use Domain\Common\Carbon;
use Domain\Permission\Permission;
use Domain\Permission\PermissionGroup;
use Infrastructure\Permission\PermissionGroupRepositoryCacheImpl;
use Lib\Exceptions\LogicException;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CacheMixin;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\DatabaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\PermissionGroupRepositoryFallbackMixin;
use Tests\Unit\Test;

/**
 * PermissionGroupRepositoryCacheImpl のテスト.
 */
final class PermissionGroupRepositoryCacheImplTest extends Test
{
    use CacheMixin;
    use CarbonMixin;
    use DatabaseMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use PermissionGroupRepositoryFallbackMixin;
    use UnitSupport;

    /**
     * @var \Domain\Permission\PermissionGroup[]
     */
    private array $permissionGroups;

    private PermissionGroupRepositoryCacheImpl $repository;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (PermissionGroupRepositoryCacheImplTest $self): void {
            $self->permissionGroups = $self->permissionGroups();
            $self->repository = app(PermissionGroupRepositoryCacheImpl::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_transactionManager(): void
    {
        $this->should('return the value using fallback repository', function (): void {
            $this->permissionGroupRepositoryFallback
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
            $expected = $this->permissionGroups[0];
            $this->cache->expects('remember')->andReturn(Seq::from($expected));

            $actual = $this->repository->lookup($expected->id);

            $this->assertSame(1, $actual->size());
            $this->assertModelStrictEquals($expected, $actual->head());
        });
        $this->should('return an entity from fallback when cache does not exist', function (): void {
            $expected = $this->permissionGroups[0];
            $this->cache->expects('remember')->andReturnUsing(fn ($key, $ttl, Closure $callback) => $callback());
            $this->permissionGroupRepositoryFallback
                ->expects('lookup')
                ->with($expected->id)
                ->andReturn(Seq::from($expected));

            $actual = $this->repository->lookup($expected->id);

            $this->assertSame(1, $actual->size());
            $this->assertModelStrictEquals($expected, $actual->head());
        });
        $this->should('cache an entity from fallback when cache does not exist', function (): void {
            $entity = $this->permissionGroups[0];
            $this->cache
                ->expects('remember')
                ->with(
                    "permissionGroup:id:{$entity->id}",
                    equalTo(Carbon::tomorrow()),
                    anInstanceOf(Closure::class)
                )
                ->andReturnUsing(fn ($key, $ttl, Closure $callback) => $callback());
            $this->permissionGroupRepositoryFallback->expects('lookup')->andReturn(Seq::from($entity));

            $this->repository->lookup($entity->id);
        });
        $this->should('throw a NotFoundException when the permissionGroup not found', function (): void {
            $this->cache->allows('remember')->andReturn(Option::none());
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
        $this->should('throw LogicException', function (): void {
            $this->assertThrows(LogicException::class, function (): void {
                $this->repository->store($this->permissionGroups[0]);
            });
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_remove(): void
    {
        $this->should('throw LogicException', function (): void {
            $this->assertThrows(LogicException::class, function (): void {
                $this->repository->remove($this->permissionGroups[0]);
            });
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_removeById(): void
    {
        $this->should('throw LogicException', function (): void {
            $this->assertThrows(LogicException::class, function (): void {
                $this->repository->removeById($this->permissionGroups[0]->id);
            });
        });
    }

    /**
     * 権限グループの定義.
     *
     * @return array
     */
    private function permissionGroups(): array
    {
        $xs = [
            [
                'id' => 1,
                'code' => 'staffs',
                'name' => 'スタッフ',
                'displayName' => 'スタッフ',
                'sortOrder' => 1,
                'permissions' => [
                    Permission::createStaffs(),
                    Permission::deleteStaffs(),
                    Permission::listStaffs(),
                    Permission::updateStaffs(),
                    Permission::viewStaffs(),
                ],
                'createdAt' => Carbon::create(2019, 12, 9, 0, 0, 0),
            ],
        ];
        return array_map(fn (array $attributes) => PermissionGroup::create($attributes), $xs);
    }
}
