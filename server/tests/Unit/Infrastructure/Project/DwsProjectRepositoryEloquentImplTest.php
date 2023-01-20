<?php
/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Project;

use App\Concretes\PermanentDatabaseTransactionManager;
use Domain\Project\DwsProject;
use Infrastructure\Project\DwsProjectRepositoryEloquentImpl;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\DatabaseMixin;
use Tests\Unit\Test;

/**
 * {@link \Infrastructure\Project\DwsProjectRepositoryEloquentImpl} のテスト.
 */
class DwsProjectRepositoryEloquentImplTest extends Test
{
    use DatabaseMixin;
    use ExamplesConsumer;
    use UnitSupport;

    private DwsProjectRepositoryEloquentImpl $repository;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (DwsProjectRepositoryEloquentImplTest $self): void {
            $self->repository = app(DwsProjectRepositoryEloquentImpl::class);
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
            $expected = $this->examples->dwsProjects[0];
            $actual = $this->repository->lookup($this->examples->dwsProjects[0]->id);

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
            $entity = DwsProject::create($this->attrs());
            $stored = $this->repository->store($entity);
            $actual = $this->repository->lookup($stored->id);
            $this->assertCount(1, $actual);
            $this->assertModelStrictEquals(
                $entity->copy(['id' => $stored->id]),
                $actual->head()
            );
        });
        $this->should('update the entity', function (): void {
            $newVersion = 2;
            $this->assertNotEquals($newVersion, $this->examples->dwsProjects[0]->version);
            $dwsProject = $this->examples->dwsProjects[0]->copy(['version' => $newVersion]);
            $this->repository->store($dwsProject);
            $actual = $this->repository->lookup($this->examples->dwsProjects[0]->id);

            $this->assertCount(1, $actual);
            $this->assertModelStrictEquals(
                $dwsProject,
                $actual->head()
            );
        });
        $this->should('return stored entity', function (): void {
            $entity = DwsProject::create($this->attrs());

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
    public function describe_remove(): void
    {
        $this->should('remove the entity', function (): void {
            $this->repository->remove($this->examples->dwsProjects[0]);
            $actual = $this->repository->lookup($this->examples->dwsProjects[0]->id);
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
            $this->repository->removeById(
                $this->examples->dwsProjects[0]->id,
                $this->examples->dwsProjects[1]->id
            );
            $dwsProject0 = $this->repository->lookup($this->examples->dwsProjects[0]->id);
            $dwsProject1 = $this->repository->lookup($this->examples->dwsProjects[1]->id);
            $this->assertCount(0, $dwsProject0);
            $this->assertCount(0, $dwsProject1);
        });
        $this->should('not remove other entities', function (): void {
            $this->repository->removeById($this->examples->dwsProjects[0]->id);
            $actual = $this->repository->lookup($this->examples->dwsProjects[0]->id);
            $this->assertCount(0, $actual);
            $this->assertTrue($this->repository->lookup($this->examples->dwsProjects[1]->id)->nonEmpty());
            $this->assertTrue($this->repository->lookup($this->examples->dwsProjects[2]->id)->nonEmpty());
        });
    }

    /**
     * ドメインモデル 計画の値を返す.
     *
     * @return array
     */
    private function attrs(): array
    {
        return [
            'organizationId' => $this->examples->organizations[0]->id,
            'contractId' => $this->examples->contracts[3]->id,
            'officeId' => $this->examples->offices[0]->id,
            'userId' => $this->examples->users[0]->id,
            'staffId' => $this->examples->staffs[0]->id,
            'writtenOn' => $this->examples->dwsProjects[0]->writtenOn,
            'effectivatedOn' => $this->examples->dwsProjects[0]->effectivatedOn,
            'requestFromUser' => $this->examples->dwsProjects[0]->requestFromUser,
            'requestFromFamily' => $this->examples->dwsProjects[0]->requestFromFamily,
            'objective' => $this->examples->dwsProjects[0]->objective,
            'programs' => $this->examples->dwsProjects[0]->programs,
            'isEnabled' => $this->examples->dwsProjects[0]->isEnabled,
            'version' => $this->examples->dwsProjects[0]->version,
            'createdAt' => $this->examples->dwsProjects[0]->createdAt,
            'updatedAt' => $this->examples->dwsProjects[0]->updatedAt,
        ];
    }
}
