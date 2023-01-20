<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Project;

use App\Concretes\PermanentDatabaseTransactionManager;
use Domain\Project\DwsProjectServiceMenu;
use Infrastructure\Project\DwsProjectServiceMenuRepositoryEloquentImpl;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\DatabaseMixin;
use Tests\Unit\Test;

/**
 * {@link \Infrastructure\Project\DwsProjectServiceMenuRepositoryEloquentImpl} のテスト.
 */
class DwsProjectServiceMenuRepositoryEloquentImplTest extends Test
{
    use DatabaseMixin;
    use ExamplesConsumer;
    use UnitSupport;

    private DwsProjectServiceMenuRepositoryEloquentImpl $repository;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (DwsProjectServiceMenuRepositoryEloquentImplTest $self): void {
            $self->repository = app(DwsProjectServiceMenuRepositoryEloquentImpl::class);
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
            $expected = $this->examples->dwsProjectServiceMenus[0];
            $actual = $this->repository->lookup($this->examples->dwsProjectServiceMenus[0]->id);

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
    public function describe_store(): void
    {
        $this->should('add the entity', function (): void {
            $entity = DwsProjectServiceMenu::create($this->attrs());
            $stored = $this->repository->store($entity);
            $actual = $this->repository->lookup($stored->id);
            $this->assertCount(1, $actual);
            $this->assertModelStrictEquals(
                $entity->copy(['id' => $stored->id]),
                $actual->head()
            );
        });
        $this->should('update the entity', function (): void {
            $newName = '新しい名称';
            $this->assertNotEquals($newName, $this->examples->dwsProjectServiceMenus[0]->name);
            $dwsProjectServiceMenu = $this->examples->dwsProjectServiceMenus[0]->copy(['name' => $newName]);
            $this->repository->store($dwsProjectServiceMenu);
            $actual = $this->repository->lookup($this->examples->dwsProjectServiceMenus[0]->id);

            $this->assertCount(1, $actual);
            $this->assertModelStrictEquals(
                $dwsProjectServiceMenu,
                $actual->head()
            );
        });
        $this->should('return stored entity', function (): void {
            $entity = DwsProjectServiceMenu::create($this->attrs());

            $stored = $this->repository->store($entity);
            $this->assertModelStrictEquals(
                $entity->copy(['id' => $stored->id]),
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
            $this->repository->removeById($this->examples->dwsProjectServiceMenus[10]->id, $this->examples->dwsProjectServiceMenus[11]->id);
            $dwsProjectServiceMenu0 = $this->repository->lookup($this->examples->dwsProjectServiceMenus[10]->id);
            $this->assertCount(0, $dwsProjectServiceMenu0);
            $dwsProjectServiceMenu1 = $this->repository->lookup($this->examples->dwsProjectServiceMenus[11]->id);
            $this->assertCount(0, $dwsProjectServiceMenu1);
        });
        $this->should('not remove other entities', function (): void {
            $this->repository->removeById($this->examples->dwsProjectServiceMenus[10]->id);
            $dwsProjectServiceMenu0 = $this->repository->lookup($this->examples->dwsProjectServiceMenus[10]->id);
            $this->assertCount(0, $dwsProjectServiceMenu0);
            $dwsProjectServiceMenu1 = $this->repository->lookup($this->examples->dwsProjectServiceMenus[11]->id);
            $dwsProjectServiceMenu2 = $this->repository->lookup($this->examples->dwsProjectServiceMenus[12]->id);
            $this->assertCount(1, $dwsProjectServiceMenu1);
            $this->assertModelStrictEquals($this->examples->dwsProjectServiceMenus[11], $dwsProjectServiceMenu1->head());
            $this->assertCount(1, $dwsProjectServiceMenu2);
            $this->assertModelStrictEquals($this->examples->dwsProjectServiceMenus[12], $dwsProjectServiceMenu2->head());
        });
    }

    /**
     * ドメインモデル 障害福祉サービス：計画：サービス内容の値を返す.
     *
     * @return array
     */
    private function attrs(): array
    {
        return [
            'sortOrder' => $this->examples->dwsProjectServiceMenus[0]->sortOrder,
            'category' => $this->examples->dwsProjectServiceMenus[0]->category,
            'name' => $this->examples->dwsProjectServiceMenus[0]->name,
            'displayName' => $this->examples->dwsProjectServiceMenus[0]->displayName,
            'createdAt' => $this->examples->dwsProjectServiceMenus[0]->createdAt,
        ];
    }
}
