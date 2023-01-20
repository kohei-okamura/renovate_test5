<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Office;

use App\Concretes\PermanentDatabaseTransactionManager;
use Domain\Common\Carbon;
use Domain\Office\HomeHelpServiceCalcSpec;
use Infrastructure\Office\HomeHelpServiceCalcSpecRepositoryEloquentImpl;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\DatabaseMixin;
use Tests\Unit\Test;

/**
 * {@link \Infrastructure\Office\HomeHelpServiceCalcSpecRepositoryEloquentImpl} のテスト.
 */
class HomeHelpServiceCalcSpecRepositoryEloquentImplTest extends Test
{
    use DatabaseMixin;
    use ExamplesConsumer;
    use UnitSupport;

    private HomeHelpServiceCalcSpecRepositoryEloquentImpl $repository;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (HomeHelpServiceCalcSpecRepositoryEloquentImplTest $self): void {
            $self->repository = app(HomeHelpServiceCalcSpecRepositoryEloquentImpl::class);
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
            $homeHelpServiceCalcSpec = $this->examples->homeHelpServiceCalcSpecs[0];
            $expected = $homeHelpServiceCalcSpec;
            $actual = $this->repository->lookup($homeHelpServiceCalcSpec->id);
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
            ] + $this->examples->homeHelpServiceCalcSpecs[0]->toAssoc();
            $domain = HomeHelpServiceCalcSpec::create($attrs);

            $stored = $this->repository->store($domain);

            $actual = $this->repository->lookup($stored->id);

            $this->assertCount(1, $actual);
            $this->assertModelStrictEquals($stored, $actual->head());
        });
        $this->should('update the entity', function (): void {
            $this->assertNotEquals(Carbon::now(), $this->examples->homeHelpServiceCalcSpecs[0]->createdAt);
            $homeHelpServiceCalcSpec = $this->examples->homeHelpServiceCalcSpecs[0]->copy(['version' => 2]);

            $this->repository->store($homeHelpServiceCalcSpec);

            $actual = $this->repository->lookup($this->examples->homeHelpServiceCalcSpecs[0]->id);

            $this->assertCount(1, $actual);
            $this->assertModelStrictEquals(
                $homeHelpServiceCalcSpec,
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
            $this->repository->removeById($this->examples->homeHelpServiceCalcSpecs[0]->id, $this->examples->homeHelpServiceCalcSpecs[1]->id);
            $homeHelpServiceCalcSpec0 = $this->repository->lookup($this->examples->homeHelpServiceCalcSpecs[0]->id);
            $this->assertCount(0, $homeHelpServiceCalcSpec0);
            $homeHelpServiceCalcSpec1 = $this->repository->lookup($this->examples->homeHelpServiceCalcSpecs[1]->id);
            $this->assertCount(0, $homeHelpServiceCalcSpec1);
        });
        $this->should('not remove other entities', function (): void {
            $this->repository->removeById($this->examples->homeHelpServiceCalcSpecs[0]->id);
            $homeHelpServiceCalcSpec0 = $this->repository->lookup($this->examples->homeHelpServiceCalcSpecs[0]->id);
            $this->assertCount(0, $homeHelpServiceCalcSpec0);

            $homeHelpServiceCalcSpec1 = $this->repository->lookup($this->examples->homeHelpServiceCalcSpecs[1]->id);
            $homeHelpServiceCalcSpec2 = $this->repository->lookup($this->examples->homeHelpServiceCalcSpecs[2]->id);
            $this->assertCount(1, $homeHelpServiceCalcSpec1);
            $this->assertModelStrictEquals($this->examples->homeHelpServiceCalcSpecs[1], $homeHelpServiceCalcSpec1->head());
            $this->assertCount(1, $homeHelpServiceCalcSpec2);
            $this->assertModelStrictEquals($this->examples->homeHelpServiceCalcSpecs[2], $homeHelpServiceCalcSpec2->head());
        });
    }
}
