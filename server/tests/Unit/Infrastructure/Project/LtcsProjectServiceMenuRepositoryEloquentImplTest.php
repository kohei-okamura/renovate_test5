<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Project;

use App\Concretes\PermanentDatabaseTransactionManager;
use Domain\Project\LtcsProjectServiceMenu;
use Infrastructure\Project\LtcsProjectServiceMenuRepositoryEloquentImpl;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\DatabaseMixin;
use Tests\Unit\Test;

/**
 * {@link \Infrastructure\Project\LtcsProjectServiceMenuRepositoryEloquentImpl} のテスト.
 */
class LtcsProjectServiceMenuRepositoryEloquentImplTest extends Test
{
    use DatabaseMixin;
    use ExamplesConsumer;
    use UnitSupport;

    private LtcsProjectServiceMenuRepositoryEloquentImpl $repository;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (LtcsProjectServiceMenuRepositoryEloquentImplTest $self): void {
            $self->repository = app(LtcsProjectServiceMenuRepositoryEloquentImpl::class);
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
            $expected = $this->examples->ltcsProjectServiceMenus[0];
            $actual = $this->repository->lookup($this->examples->ltcsProjectServiceMenus[0]->id);

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
            $entity = LtcsProjectServiceMenu::create($this->attrs());
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
            $this->assertNotEquals($newName, $this->examples->ltcsProjectServiceMenus[0]->name);
            $ltcsProjectServiceMenu = $this->examples->ltcsProjectServiceMenus[0]->copy(['name' => $newName]);
            $this->repository->store($ltcsProjectServiceMenu);
            $actual = $this->repository->lookup($this->examples->ltcsProjectServiceMenus[0]->id);

            $this->assertCount(1, $actual);
            $this->assertModelStrictEquals(
                $ltcsProjectServiceMenu,
                $actual->head()
            );
        });
        $this->should('return stored entity', function (): void {
            $entity = LtcsProjectServiceMenu::create($this->attrs());

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
            $this->repository->removeById($this->examples->ltcsProjectServiceMenus[10]->id, $this->examples->ltcsProjectServiceMenus[11]->id);
            $ltcsProjectServiceMenu0 = $this->repository->lookup($this->examples->ltcsProjectServiceMenus[10]->id);
            $this->assertCount(0, $ltcsProjectServiceMenu0);
            $ltcsProjectServiceMenu1 = $this->repository->lookup($this->examples->ltcsProjectServiceMenus[11]->id);
            $this->assertCount(0, $ltcsProjectServiceMenu1);
        });
        $this->should('not remove other entities', function (): void {
            $this->repository->removeById($this->examples->ltcsProjectServiceMenus[10]->id);
            $ltcsProjectServiceMenu0 = $this->repository->lookup($this->examples->ltcsProjectServiceMenus[10]->id);
            $this->assertCount(0, $ltcsProjectServiceMenu0);
            $ltcsProjectServiceMenu1 = $this->repository->lookup($this->examples->ltcsProjectServiceMenus[11]->id);
            $ltcsProjectServiceMenu2 = $this->repository->lookup($this->examples->ltcsProjectServiceMenus[12]->id);
            $this->assertCount(1, $ltcsProjectServiceMenu1);
            $this->assertModelStrictEquals($this->examples->ltcsProjectServiceMenus[11], $ltcsProjectServiceMenu1->head());
            $this->assertCount(1, $ltcsProjectServiceMenu2);
            $this->assertModelStrictEquals($this->examples->ltcsProjectServiceMenus[12], $ltcsProjectServiceMenu2->head());
        });
    }

    /**
     * ドメインモデル 介護保険サービス：計画：サービス内容の値を返す.
     *
     * @return array
     */
    private function attrs(): array
    {
        return [
            'sortOrder' => $this->examples->ltcsProjectServiceMenus[0]->sortOrder,
            'category' => $this->examples->ltcsProjectServiceMenus[0]->category,
            'name' => $this->examples->ltcsProjectServiceMenus[0]->name,
            'displayName' => $this->examples->ltcsProjectServiceMenus[0]->displayName,
            'createdAt' => $this->examples->ltcsProjectServiceMenus[0]->createdAt,
        ];
    }
}
