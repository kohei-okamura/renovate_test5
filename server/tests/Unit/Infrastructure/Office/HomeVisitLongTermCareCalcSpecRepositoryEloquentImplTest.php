<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Office;

use App\Concretes\PermanentDatabaseTransactionManager;
use Domain\Common\Carbon;
use Domain\Office\HomeVisitLongTermCareCalcSpec;
use Infrastructure\Office\HomeVisitLongTermCareCalcSpecRepositoryEloquentImpl;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\DatabaseMixin;
use Tests\Unit\Test;

/**
 * {@link \Infrastructure\Office\HomeVisitLongTermCareCalcSpecRepositoryEloquentImpl} のテスト.
 */
class HomeVisitLongTermCareCalcSpecRepositoryEloquentImplTest extends Test
{
    use DatabaseMixin;
    use ExamplesConsumer;
    use UnitSupport;

    private HomeVisitLongTermCareCalcSpecRepositoryEloquentImpl $repository;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (HomeVisitLongTermCareCalcSpecRepositoryEloquentImplTest $self): void {
            $self->repository = app(HomeVisitLongTermCareCalcSpecRepositoryEloquentImpl::class);
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
            $homeVisitLongTermCareCalcSpec = $this->examples->homeVisitLongTermCareCalcSpecs[0];
            $expected = $homeVisitLongTermCareCalcSpec;
            $actual = $this->repository->lookup($homeVisitLongTermCareCalcSpec->id);
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
        $this->should('add the entity to repository when it does not exist in repository', function (): void {
            $attrs = [
                'id' => self::NOT_EXISTING_ID,
            ] + $this->examples->homeVisitLongTermCareCalcSpecs[0]->toAssoc();
            $domain = HomeVisitLongTermCareCalcSpec::create($attrs);

            $stored = $this->repository->store($domain);

            $actual = $this->repository->lookup($stored->id);

            $this->assertCount(1, $actual);
            $this->assertModelStrictEquals($stored, $actual->head());
        });
        $this->should('update the entity', function (): void {
            $this->assertNotEquals(Carbon::now(), $this->examples->homeVisitLongTermCareCalcSpecs[0]->createdAt);
            $homeVisitLongTermCareCalcSpec = $this->examples->homeVisitLongTermCareCalcSpecs[0]->copy(['version' => 2]);

            $this->repository->store($homeVisitLongTermCareCalcSpec);

            $actual = $this->repository->lookup($this->examples->homeVisitLongTermCareCalcSpecs[0]->id);

            $this->assertCount(1, $actual);
            $this->assertModelStrictEquals(
                $homeVisitLongTermCareCalcSpec,
                $actual->head()
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
            $this->repository->removeById($this->examples->homeVisitLongTermCareCalcSpecs[0]->id, $this->examples->homeVisitLongTermCareCalcSpecs[1]->id);
            $homeVisitLongTermCareCalcSpec0 = $this->repository->lookup($this->examples->homeVisitLongTermCareCalcSpecs[0]->id);
            $this->assertCount(0, $homeVisitLongTermCareCalcSpec0);
            $homeVisitLongTermCareCalcSpec1 = $this->repository->lookup($this->examples->homeVisitLongTermCareCalcSpecs[1]->id);
            $this->assertCount(0, $homeVisitLongTermCareCalcSpec1);
        });
        $this->should('not remove other entities', function (): void {
            $this->repository->removeById($this->examples->homeVisitLongTermCareCalcSpecs[0]->id);
            $homeVisitLongTermCareCalcSpec0 = $this->repository->lookup($this->examples->homeVisitLongTermCareCalcSpecs[0]->id);
            $this->assertCount(0, $homeVisitLongTermCareCalcSpec0);

            $homeVisitLongTermCareCalcSpec1 = $this->repository->lookup($this->examples->homeVisitLongTermCareCalcSpecs[1]->id);
            $homeVisitLongTermCareCalcSpec2 = $this->repository->lookup($this->examples->homeVisitLongTermCareCalcSpecs[2]->id);
            $this->assertCount(1, $homeVisitLongTermCareCalcSpec1);
            $this->assertModelStrictEquals($this->examples->homeVisitLongTermCareCalcSpecs[1], $homeVisitLongTermCareCalcSpec1->head());
            $this->assertCount(1, $homeVisitLongTermCareCalcSpec2);
            $this->assertModelStrictEquals($this->examples->homeVisitLongTermCareCalcSpecs[2], $homeVisitLongTermCareCalcSpec2->head());
        });
    }
}
