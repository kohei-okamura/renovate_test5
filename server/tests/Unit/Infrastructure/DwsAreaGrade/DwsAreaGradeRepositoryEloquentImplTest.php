<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Infrastructure\DwsAreaGrade;

use App\Concretes\PermanentDatabaseTransactionManager;
use Domain\DwsAreaGrade\DwsAreaGrade as DomainDwsAreaGrade;
use Infrastructure\DwsAreaGrade\DwsAreaGradeRepositoryEloquentImpl;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\DatabaseMixin;
use Tests\Unit\Test;

/**
 * DwsAreaGradeRepositoryEloquentImpl のテスト.
 */
class DwsAreaGradeRepositoryEloquentImplTest extends Test
{
    use DatabaseMixin;
    use ExamplesConsumer;
    use UnitSupport;

    private DwsAreaGradeRepositoryEloquentImpl $repository;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (DwsAreaGradeRepositoryEloquentImplTest $self): void {
            $self->repository = app(DwsAreaGradeRepositoryEloquentImpl::class);
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
            $expected = $this->examples->dwsAreaGrades[0];
            $actual = $this->repository->lookup($this->examples->dwsAreaGrades[0]->id);
            $this->assertCount(1, $actual);
            $this->assertModelStrictEquals($expected, $actual->head());
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
                'code' => $this->examples->dwsAreaGrades[0]->code,
                'name' => '1級地',
            ];
            $entity = DomainDwsAreaGrade::create($attrs);
            $stored = $this->repository->store($entity);
            $actual = $this->repository->lookup($stored->id);
            $this->assertModelStrictEquals($stored, $actual->head());
        });
        $this->should('update the entity', function (): void {
            $this->assertNotEquals('3級地', $this->examples->dwsAreaGrades[0]->name);
            $dwsAreaGrade = $this->examples->dwsAreaGrades[0]->copy(['version' => 2]);
            $this->assertModelStrictEquals(
                $dwsAreaGrade,
                $this->repository->store($dwsAreaGrade)
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
            $this->repository->remove($this->examples->dwsAreaGrades[0]);
            $actual = $this->repository->lookup($this->examples->dwsAreaGrades[0]->id);
            $this->assertCount(0, $actual);
        });
        $this->should('not remove other entities', function (): void {
            $this->repository->remove($this->examples->dwsAreaGrades[0]);
            $this->assertTrue($this->repository->lookup($this->examples->dwsAreaGrades[1]->id)->nonEmpty());
            $this->assertTrue($this->repository->lookup($this->examples->dwsAreaGrades[2]->id)->nonEmpty());
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_removeById(): void
    {
        $this->should('remove entities', function (): void {
            $this->repository->removeById($this->examples->dwsAreaGrades[2]->id, $this->examples->dwsAreaGrades[3]->id);
            $dwsAreaGrade2 = $this->repository->lookup($this->examples->dwsAreaGrades[2]->id);
            $dwsAreaGrade3 = $this->repository->lookup($this->examples->dwsAreaGrades[3]->id);
            $this->assertCount(0, $dwsAreaGrade2);
            $this->assertCount(0, $dwsAreaGrade3);
        });
        $this->should('not remove other entities', function (): void {
            $this->repository->removeById($this->examples->dwsAreaGrades[2]->id);
            $actual = $this->repository->lookup($this->examples->dwsAreaGrades[2]->id);
            $this->assertCount(0, $actual);
            $this->assertTrue($this->repository->lookup($this->examples->dwsAreaGrades[0]->id)->nonEmpty());
            $this->assertTrue($this->repository->lookup($this->examples->dwsAreaGrades[1]->id)->nonEmpty());
        });
    }
}
