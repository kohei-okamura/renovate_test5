<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Calling;

use App\Concretes\PermanentDatabaseTransactionManager;
use Domain\Calling\CallingLog as DomainCallingLog;
use Domain\Common\Carbon;
use Infrastructure\Calling\CallingLogRepositoryEloquentImpl;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\DatabaseMixin;
use Tests\Unit\Test;

/**
 * CallingLogRepositoryEloquentImpl のテスト.
 */
class CallingLogRepositoryEloquentImplTest extends Test
{
    use CarbonMixin;
    use DatabaseMixin;
    use ExamplesConsumer;
    use UnitSupport;

    private CallingLogRepositoryEloquentImpl $repository;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (CallingLogRepositoryEloquentImplTest $self): void {
            $self->repository = app(CallingLogRepositoryEloquentImpl::class);
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
            $expected = $this->examples->callingLogs[0];
            $actual = $this->repository->lookup($this->examples->callingLogs[0]->id);
            $this->assertEquals(1, $actual->size());
            $this->assertModelStrictEquals(
                $expected,
                $actual->head()
            );
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
        $this->should('add the entity to repository when it does not exist in repository', function (): void {
            $attrs = [
                'id' => self::NOT_EXISTING_ID,
                'callingId' => $this->examples->callingLogs[0]->id,
                'callingType' => $this->examples->callingLogs[0]->callingType,
                'isSucceeded' => $this->examples->callingLogs[0]->isSucceeded,
                'createdAt' => Carbon::now(),
            ];
            $entity = DomainCallingLog::create($attrs);
            $stored = $this->repository->store($entity);
            $actual = $this->repository->lookup($stored->id);
            $this->assertCount(1, $actual);
            $this->assertModelStrictEquals(
                $stored,
                $actual->head()
            );
        });
        $this->should('update the entity', function (): void {
            $this->assertNotEquals(Carbon::now(), $this->examples->callingLogs[0]);
            $callingLog = $this->examples->callingLogs[0]->copy();
            $this->repository->store($callingLog);
            $actual = $this->repository->lookup($this->examples->callingLogs[0]->id);
            $this->assertCount(1, $actual);
            $this->assertModelStrictEquals(
                $callingLog,
                $actual->head()
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
            $callingLog = $this->examples->callingLogs[0];
            $this->repository->remove($callingLog);
            $actual = $this->repository->lookup($this->examples->callingLogs[0]->id);
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
            $this->repository->removeById($this->examples->callingLogs[0]->id, $this->examples->callingLogs[1]->id);
            $callingLog0 = $this->repository->lookup($this->examples->callingLogs[0]->id);
            $this->assertCount(0, $callingLog0);
            $callingLog1 = $this->repository->lookup($this->examples->callingLogs[1]->id);
            $this->assertCount(0, $callingLog1);
        });
        $this->should('not remove other entities', function (): void {
            $this->repository->removeById($this->examples->callingLogs[0]->id);
            $callingLog0 = $this->repository->lookup($this->examples->callingLogs[0]->id);
            $this->assertCount(0, $callingLog0);
            $callingLog1 = $this->repository->lookup($this->examples->callingLogs[1]->id);
            $callingLog2 = $this->repository->lookup($this->examples->callingLogs[2]->id);
            $this->assertCount(1, $callingLog1);
            $this->assertModelStrictEquals($this->examples->callingLogs[1], $callingLog1->head());
            $this->assertCount(1, $callingLog2);
            $this->assertModelStrictEquals($this->examples->callingLogs[2], $callingLog2->head());
        });
    }
}
