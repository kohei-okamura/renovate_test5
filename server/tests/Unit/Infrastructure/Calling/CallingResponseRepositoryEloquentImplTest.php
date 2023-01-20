<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Calling;

use App\Concretes\PermanentDatabaseTransactionManager;
use Domain\Calling\CallingResponse as DomainCallingResponse;
use Domain\Common\Carbon;
use Infrastructure\Calling\CallingResponseRepositoryEloquentImpl;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\DatabaseMixin;
use Tests\Unit\Test;

/**
 * CallingRepositoryEloquentImpl のテスト.
 */
class CallingResponseRepositoryEloquentImplTest extends Test
{
    use CarbonMixin;
    use DatabaseMixin;
    use ExamplesConsumer;
    use UnitSupport;

    private CallingResponseRepositoryEloquentImpl $repository;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (CallingResponseRepositoryEloquentImplTest $self): void {
            $self->repository = app(CallingResponseRepositoryEloquentImpl::class);
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
            $expected = $this->examples->callingResponses[0];
            $actual = $this->repository->lookup($this->examples->callingResponses[0]->id);
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
                'callingId' => $this->examples->callings[0]->id,
                'createdAt' => Carbon::now(),
            ];
            $entity = DomainCallingResponse::create($attrs);
            $stored = $this->repository->store($entity);
            $actual = $this->repository->lookup($stored->id);
            $this->assertCount(1, $actual);
            $this->assertModelStrictEquals(
                $stored,
                $actual->head()
            );
        });
        $this->should('update the entity', function (): void {
            $this->assertNotEquals(Carbon::now(), $this->examples->callingResponses[0]);
            $callingResponse = $this->examples->callingResponses[0]->copy();
            $this->repository->store($callingResponse);
            $actual = $this->repository->lookup($this->examples->callingResponses[0]->id);
            $this->assertCount(1, $actual);
            $this->assertModelStrictEquals(
                $callingResponse,
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
            $callingResponse = $this->examples->callingResponses[0];
            $this->repository->remove($callingResponse);
            $actual = $this->repository->lookup($this->examples->callingResponses[0]->id);
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
                $this->examples->callingResponses[0]->id,
                $this->examples->callingResponses[1]->id
            );
            $callingResponse0 = $this->repository->lookup($this->examples->callingResponses[0]->id);
            $this->assertCount(0, $callingResponse0);
            $callingResponse1 = $this->repository->lookup($this->examples->callingResponses[1]->id);
            $this->assertCount(0, $callingResponse1);
        });
        $this->should('not remove other entities', function (): void {
            $this->repository->removeById($this->examples->callingResponses[0]->id);
            $callingResponse0 = $this->repository->lookup($this->examples->callingResponses[0]->id);
            $this->assertCount(0, $callingResponse0);
            $callingResponse1 = $this->repository->lookup($this->examples->callingResponses[1]->id);
            $callingResponse2 = $this->repository->lookup($this->examples->callingResponses[2]->id);
            $this->assertCount(1, $callingResponse1);
            $this->assertModelStrictEquals($this->examples->callingResponses[1], $callingResponse1->head());
            $this->assertCount(1, $callingResponse2);
            $this->assertModelStrictEquals($this->examples->callingResponses[2], $callingResponse2->head());
        });
    }
}
