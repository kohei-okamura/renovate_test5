<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Infrastructure\OwnExpenseProgram;

use App\Concretes\PermanentDatabaseTransactionManager;
use Domain\Common\Carbon;
use Domain\OwnExpenseProgram\OwnExpenseProgram;
use Infrastructure\OwnExpenseProgram\OwnExpenseProgramRepositoryEloquentImpl;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Fixtures\OrganizationFixture;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\DatabaseMixin;
use Tests\Unit\Test;

/**
 * OwnExpenseProgramRepositoryEloquentImpl のテスト.
 */
class OwnExpenseProgramRepositoryEloquentImplTest extends Test
{
    use CarbonMixin;
    use DatabaseMixin;
    use ExamplesConsumer;
    use OrganizationFixture;
    use UnitSupport;

    private OwnExpenseProgramRepositoryEloquentImpl $repository;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (OwnExpenseProgramRepositoryEloquentImplTest $self): void {
            $self->repository = app(OwnExpenseProgramRepositoryEloquentImpl::class);
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
            $expected = $this->examples->ownExpensePrograms[0];
            $actual = $this->repository->lookup($this->examples->ownExpensePrograms[0]->id);

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
    public function describe_store()
    {
        $this->should('add the entity', function (): void {
            $attrs = [
                'organizationId' => $this->examples->ownExpensePrograms[0]->organizationId,
                'officeId' => $this->examples->ownExpensePrograms[0]->officeId,
                'name' => $this->examples->ownExpensePrograms[0]->name,
                'durationMinutes' => $this->examples->ownExpensePrograms[0]->durationMinutes,
                'fee' => $this->examples->ownExpensePrograms[0]->fee,
                'note' => $this->examples->ownExpensePrograms[0]->note,
                'isEnabled' => true,
                'version' => 1,
                'createdAt' => Carbon::now(),
                'updatedAt' => Carbon::now(),
            ];
            $entity = OwnExpenseProgram::create($attrs);
            $stored = $this->repository->store($entity);
            $actual = $this->repository->lookup($stored->id);
            $this->assertCount(1, $actual);
            $this->assertModelStrictEquals(
                $entity->copy(['id' => $stored->id]),
                $actual->head()
            );
        });
        $this->should('update the entity', function (): void {
            $newName = '名称2';
            $this->assertNotEquals($newName, $this->examples->ownExpensePrograms[0]->name);
            $ownExpenseProgram = $this->examples->ownExpensePrograms[0]->copy(['name' => $newName, 'version' => 2]);
            $this->repository->store($ownExpenseProgram);
            $actual = $this->repository->lookup($this->examples->ownExpensePrograms[0]->id);

            $this->assertCount(1, $actual);
            $this->assertModelStrictEquals(
                $ownExpenseProgram,
                $actual->head()
            );
        });
        $this->should('return stored entity', function (): void {
            $attrs = [
                'organizationId' => $this->examples->ownExpensePrograms[0]->organizationId,
                'officeId' => $this->examples->ownExpensePrograms[0]->officeId,
                'name' => $this->examples->ownExpensePrograms[0]->name,
                'durationMinutes' => $this->examples->ownExpensePrograms[0]->durationMinutes,
                'fee' => $this->examples->ownExpensePrograms[0]->fee,
                'note' => $this->examples->ownExpensePrograms[0]->note,
                'isEnabled' => true,
                'version' => 1,
                'createdAt' => Carbon::now(),
                'updatedAt' => Carbon::now(),
            ];
            $entity = OwnExpenseProgram::create($attrs);

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
            $this->repository->removeById($this->examples->ownExpensePrograms[4]->id, $this->examples->ownExpensePrograms[5]->id);
            $ownExpenseProgram0 = $this->repository->lookup($this->examples->ownExpensePrograms[4]->id);
            $this->assertCount(0, $ownExpenseProgram0);
            $ownExpenseProgram1 = $this->repository->lookup($this->examples->ownExpensePrograms[5]->id);
            $this->assertCount(0, $ownExpenseProgram1);
        });
        $this->should('not remove other entities', function (): void {
            $this->repository->removeById($this->examples->ownExpensePrograms[4]->id);
            $ownExpenseProgram0 = $this->repository->lookup($this->examples->ownExpensePrograms[4]->id);
            $this->assertCount(0, $ownExpenseProgram0);
            $ownExpenseProgram1 = $this->repository->lookup($this->examples->ownExpensePrograms[5]->id);
            $ownExpenseProgram2 = $this->repository->lookup($this->examples->ownExpensePrograms[2]->id);
            $this->assertCount(1, $ownExpenseProgram1);
            $this->assertModelStrictEquals($this->examples->ownExpensePrograms[5], $ownExpenseProgram1->head());
            $this->assertCount(1, $ownExpenseProgram2);
            $this->assertModelStrictEquals($this->examples->ownExpensePrograms[2], $ownExpenseProgram2->head());
        });
    }
}
