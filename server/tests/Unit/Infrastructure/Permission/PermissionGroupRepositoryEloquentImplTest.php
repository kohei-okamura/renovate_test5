<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Permission;

use App\Concretes\PermanentDatabaseTransactionManager;
use Infrastructure\Permission\PermissionGroupRepositoryEloquentImpl;
use Lib\Exceptions\LogicException;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\DatabaseMixin;
use Tests\Unit\Test;

/**
 * PermissionGroupRepositoryEloquentImpl のテスト.
 */
class PermissionGroupRepositoryEloquentImplTest extends Test
{
    use DatabaseMixin;
    use ExamplesConsumer;
    use UnitSupport;

    /** @var \Domain\Permission\PermissionGroup[] */
    private array $permissionGroups;

    private PermissionGroupRepositoryEloquentImpl $repository;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (PermissionGroupRepositoryEloquentImplTest $self): void {
            $self->repository = app(PermissionGroupRepositoryEloquentImpl::class);
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
            $actual = $this->repository->lookup($this->examples->permissionGroups[0]->id);
            $this->assertSame(1, $actual->size());
            $this->assertModelStrictEquals(
                $this->examples->permissionGroups[0],
                $actual->head()
            );
        });
        $this->should('return empty seq NotFoundException when the id not exists in db', function (): void {
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
                $this->repository->store($this->examples->permissionGroups[0]);
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
                $this->repository->remove($this->examples->permissionGroups[0]);
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
                $this->repository->removeById($this->examples->permissionGroups[0]->id);
            });
        });
    }
}
