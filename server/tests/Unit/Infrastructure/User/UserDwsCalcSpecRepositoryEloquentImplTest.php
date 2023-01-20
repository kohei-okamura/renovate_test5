<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Infrastructure\User;

use App\Concretes\PermanentDatabaseTransactionManager;
use Domain\Common\Carbon;
use Domain\User\DwsUserLocationAddition;
use Domain\User\UserDwsCalcSpec;
use Infrastructure\User\UserDwsCalcSpecRepositoryEloquentImpl;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\DatabaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 *  {@link \Infrastructure\User\UserDwsCalcSpecRepositoryEloquentImpl} のテスト.
 */
final class UserDwsCalcSpecRepositoryEloquentImplTest extends Test
{
    use CarbonMixin;
    use DatabaseMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use UnitSupport;

    private UserDwsCalcSpecRepositoryEloquentImpl $repository;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachTest(function (UserDwsCalcSpecRepositoryEloquentImplTest $self): void {
            $self->repository = app(UserDwsCalcSpecRepositoryEloquentImpl::class);
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
            $expected = $this->examples->userDwsCalcSpecs[0];
            $actual = $this->repository->lookup($this->examples->userDwsCalcSpecs[0]->id);

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
            $entity = new UserDwsCalcSpec(
                id: null,
                userId: $this->examples->users[0]->id,
                effectivatedOn: Carbon::now(),
                locationAddition: DwsUserLocationAddition::none(),
                isEnabled: true,
                version: 1,
                createdAt: Carbon::now(),
                updatedAt: Carbon::now(),
            );
            $stored = $this->repository->store($entity);
            $actual = $this->repository->lookup($stored->id);
            $this->assertCount(1, $actual);
            $this->assertModelStrictEquals(
                $entity->copy(['id' => $stored->id]),
                $actual->head()
            );
        });
        $this->should('update the entity', function (): void {
            $newDwsUserLocationAddition = DwsUserLocationAddition::specifiedArea();
            $this->assertNotEquals($newDwsUserLocationAddition, $this->examples->userDwsCalcSpecs[0]->locationAddition);
            $userDwsCalcSpec = $this->examples->userDwsCalcSpecs[0]->copy([
                'locationAddition' => $newDwsUserLocationAddition,
                'version' => 2,
            ]);
            $this->repository->store($userDwsCalcSpec);
            $actual = $this->repository->lookup($this->examples->userDwsCalcSpecs[0]->id);

            $this->assertCount(1, $actual);
            $this->assertModelStrictEquals(
                $userDwsCalcSpec,
                $actual->head()
            );
        });
        $this->should('return stored entity', function (): void {
            $entity = new UserDwsCalcSpec(
                id: null,
                userId: $this->examples->users[0]->id,
                effectivatedOn: Carbon::now(),
                locationAddition: DwsUserLocationAddition::none(),
                isEnabled: true,
                version: 1,
                createdAt: Carbon::now(),
                updatedAt: Carbon::now(),
            );

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
            $this->repository->removeById($this->examples->userDwsCalcSpecs[2]->id, $this->examples->userDwsCalcSpecs[3]->id);
            $userDwsCalcSpec0 = $this->repository->lookup($this->examples->userDwsCalcSpecs[2]->id);
            $this->assertCount(0, $userDwsCalcSpec0);
            $userDwsCalcSpec1 = $this->repository->lookup($this->examples->userDwsCalcSpecs[3]->id);
            $this->assertCount(0, $userDwsCalcSpec1);
        });
        $this->should('not remove other entities', function (): void {
            $this->repository->removeById($this->examples->userDwsCalcSpecs[2]->id);
            $userDwsCalcSpec0 = $this->repository->lookup($this->examples->userDwsCalcSpecs[2]->id);
            $this->assertCount(0, $userDwsCalcSpec0);
            $userDwsCalcSpec1 = $this->repository->lookup($this->examples->userDwsCalcSpecs[3]->id);
            $userDwsCalcSpec2 = $this->repository->lookup($this->examples->userDwsCalcSpecs[1]->id);
            $this->assertCount(1, $userDwsCalcSpec1);
            $this->assertModelStrictEquals($this->examples->userDwsCalcSpecs[3], $userDwsCalcSpec1->head());
            $this->assertCount(1, $userDwsCalcSpec2);
            $this->assertModelStrictEquals($this->examples->userDwsCalcSpecs[1], $userDwsCalcSpec2->head());
        });
    }
}
