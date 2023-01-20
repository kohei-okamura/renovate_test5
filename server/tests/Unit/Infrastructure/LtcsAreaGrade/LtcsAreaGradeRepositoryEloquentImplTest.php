<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Infrastructure\LtcsAreaGrade;

use App\Concretes\PermanentDatabaseTransactionManager;
use Domain\LtcsAreaGrade\LtcsAreaGrade as DomainLtcsAreaGrade;
use Infrastructure\LtcsAreaGrade\LtcsAreaGradeRepositoryEloquentImpl;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\DatabaseMixin;
use Tests\Unit\Test;

/**
 * LtcsAreaGradeRepositoryEloquentImpl のテスト.
 */
class LtcsAreaGradeRepositoryEloquentImplTest extends Test
{
    use DatabaseMixin;
    use ExamplesConsumer;
    use UnitSupport;

    private LtcsAreaGradeRepositoryEloquentImpl $repository;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (LtcsAreaGradeRepositoryEloquentImplTest $self): void {
            $self->repository = app(LtcsAreaGradeRepositoryEloquentImpl::class);
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
            $actual = $this->repository->lookup($this->examples->ltcsAreaGrades[0]->id);
            $this->assertCount(1, $actual);
            $this->assertModelStrictEquals($this->examples->ltcsAreaGrades[0], $actual->head());
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
                'code' => $this->examples->ltcsAreaGrades[0]->code,
                'name' => '関東',
            ];
            $entity = DomainLtcsAreaGrade::create($attrs);
            $stored = $this->repository->store($entity);
            $actual = $this->repository->lookup($stored->id);
            $this->assertCount(1, $actual);
            $this->assertModelStrictEquals($stored, $actual->head());
        });
        $this->should('update the entity', function (): void {
            $this->assertNotEquals('東海', $this->examples->ltcsAreaGrades[0]->name);
            $ltcsAreaGrade = $this->examples->ltcsAreaGrades[0]->copy(['name' => '東海']);
            $this->repository->store($ltcsAreaGrade);
            $actual = $this->repository->lookup($this->examples->ltcsAreaGrades[0]->id);
            $this->assertCount(1, $actual);
            $this->assertModelStrictEquals($ltcsAreaGrade, $actual->head());
        });
        $this->should('return stored entity', function (): void {
            $entity = $this->examples->ltcsAreaGrades[0]->copy(['name' => '東海']);
            $this->assertNotEquals('東海', $this->examples->ltcsAreaGrades[0]->name);
            $this->assertModelStrictEquals($entity, $this->repository->store($entity));
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_remove(): void
    {
        $this->should('remove the entity', function (): void {
            $this->repository->remove($this->examples->ltcsAreaGrades[0]);
            $actual = $this->repository->lookup($this->examples->ltcsAreaGrades[0]->id);
            $this->assertCount(0, $actual);
        });
        $this->should('not remove other entities', function (): void {
            $this->repository->remove($this->examples->ltcsAreaGrades[0]);
            $this->assertTrue($this->repository->lookup($this->examples->ltcsAreaGrades[1]->id)->nonEmpty());
            $this->assertTrue($this->repository->lookup($this->examples->ltcsAreaGrades[2]->id)->nonEmpty());
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
                $this->examples->ltcsAreaGrades[2]->id,
                $this->examples->ltcsAreaGrades[3]->id
            );
            $ltcsAreaGrade2 = $this->repository->lookup($this->examples->ltcsAreaGrades[2]->id);
            $ltcsAreaGrade3 = $this->repository->lookup($this->examples->ltcsAreaGrades[3]->id);
            $this->assertCount(0, $ltcsAreaGrade2);
            $this->assertCount(0, $ltcsAreaGrade3);
        });
        $this->should('not remove other entities', function (): void {
            $this->repository->removeById($this->examples->ltcsAreaGrades[2]->id);
            $actual = $this->repository->lookup($this->examples->ltcsAreaGrades[2]->id);
            $this->assertCount(0, $actual);
            $this->assertTrue($this->repository->lookup($this->examples->ltcsAreaGrades[0]->id)->nonEmpty());
            $this->assertTrue($this->repository->lookup($this->examples->ltcsAreaGrades[1]->id)->nonEmpty());
        });
    }
}
