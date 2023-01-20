<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Role;

use App\Concretes\PermanentDatabaseTransactionManager;
use Domain\Common\Carbon;
use Domain\Permission\Permission;
use Domain\Role\Role as DomainRole;
use Domain\Role\RoleScope;
use Infrastructure\Role\RoleRepositoryEloquentImpl;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\DatabaseMixin;
use Tests\Unit\Test;

/**
 * RoleRepositoryEloquentImpl のテスト.
 */
class RoleRepositoryEloquentImplTest extends Test
{
    use DatabaseMixin;
    use ExamplesConsumer;
    use UnitSupport;

    private RoleRepositoryEloquentImpl $repository;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (RoleRepositoryEloquentImplTest $self): void {
            $self->repository = app(RoleRepositoryEloquentImpl::class);
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
            $actual = $this->repository->lookup($this->examples->roles[0]->id);

            $this->assertSame(1, $actual->size());
            $this->assertModelStrictEquals($this->examples->roles[0], $actual->head());
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
        $this->should('add the entity to repository when it does not exist in repository', function (): void {
            $attrs = [
                'id' => self::NOT_EXISTING_ID,
                'organizationId' => $this->examples->organizations[0]->id,
                'permissions' => [Permission::listStaffs(), Permission::viewStaffs(), Permission::createStaffs()],
                'name' => '管理者',
                'isSystemAdmin' => false,
                'scope' => RoleScope::whole(),
                'sortOrder' => 4,
                'createdAt' => Carbon::create(2019, 5, 5, 5, 5, 5),
                'updatedAt' => Carbon::create(2019, 6, 6, 6, 6, 6),
            ];
            $entity = DomainRole::create($attrs);

            $stored = $this->repository->store($entity);

            $actual = $this->repository->lookup($stored->id);
            $this->assertSame(1, $actual->size());
            $this->assertModelStrictEquals($stored, $actual->head());
        });
        $this->should('update the entity', function (): void {
            $this->assertNotEquals('エリアマネージャー', $this->examples->roles[0]->name);
            $role = $this->examples->roles[0]->copy([
                'name' => 'エリアマネージャー',
                'version' => 2,
                'permissions' => [
                    Permission::deleteStaffs(),
                    Permission::listStaffs(),
                    Permission::viewStaffs(),
                ],
            ]);

            $this->repository->store($role);

            $actual = $this->repository->lookup($this->examples->roles[0]->id);
            $this->assertSame(1, $actual->size());
            $this->assertModelStrictEquals($role, $actual->head());
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_remove(): void
    {
        $this->should('remove the entity', function (): void {
            $this->repository->remove($this->examples->roles[2]);
            $actual = $this->repository->lookup($this->examples->roles[2]->id);
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
            $this->repository->removeById($this->examples->roles[2]->id, $this->examples->roles[3]->id);
            $role2 = $this->repository->lookup($this->examples->roles[2]->id);
            $this->assertCount(0, $role2);
            $role3 = $this->repository->lookup($this->examples->roles[3]->id);
            $this->assertCount(0, $role3);
        });
        $this->should('not remove other entities', function (): void {
            $this->repository->removeById($this->examples->roles[2]->id);

            $role2 = $this->repository->lookup($this->examples->roles[2]->id);
            $role0 = $this->repository->lookup($this->examples->roles[0]->id);
            $role1 = $this->repository->lookup($this->examples->roles[1]->id);
            $this->assertCount(0, $role2);
            $this->assertCount(1, $role0);
            $this->assertModelStrictEquals($this->examples->roles[0], $role0->head());
            $this->assertCount(1, $role1);
            $this->assertModelStrictEquals($this->examples->roles[1], $role1->head());
        });
    }
}
