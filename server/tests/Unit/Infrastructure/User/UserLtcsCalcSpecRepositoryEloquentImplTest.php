<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Infrastructure\User;

use App\Concretes\PermanentDatabaseTransactionManager;
use Domain\Common\Carbon;
use Domain\User\LtcsUserLocationAddition;
use Domain\User\UserLtcsCalcSpec;
use Infrastructure\User\UserLtcsCalcSpecRepositoryEloquentImpl;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\DatabaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 *  {@link \Infrastructure\User\UserLtcsCalcSpecRepositoryEloquentImpl} のテスト.
 */
final class UserLtcsCalcSpecRepositoryEloquentImplTest extends Test
{
    use CarbonMixin;
    use DatabaseMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use UnitSupport;

    private UserLtcsCalcSpecRepositoryEloquentImpl $repository;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachTest(function (UserLtcsCalcSpecRepositoryEloquentImplTest $self): void {
            $self->repository = app(UserLtcsCalcSpecRepositoryEloquentImpl::class);
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
            $expected = $this->examples->userLtcsCalcSpecs[0];
            $actual = $this->repository->lookup($this->examples->userLtcsCalcSpecs[0]->id);

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
            $entity = new UserLtcsCalcSpec(
                id: null,
                userId: $this->examples->users[0]->id,
                effectivatedOn: Carbon::now(),
                locationAddition: LtcsUserLocationAddition::none(),
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
            $newLtcsUserLocationAddition = LtcsUserLocationAddition::mountainousArea();
            $this->assertNotEquals($newLtcsUserLocationAddition, $this->examples->userLtcsCalcSpecs[0]->locationAddition);
            $userLtcsCalcSpec = $this->examples->userLtcsCalcSpecs[0]->copy([
                'locationAddition' => $newLtcsUserLocationAddition,
                'version' => 2,
            ]);
            $this->repository->store($userLtcsCalcSpec);
            $actual = $this->repository->lookup($this->examples->userLtcsCalcSpecs[0]->id);

            $this->assertCount(1, $actual);
            $this->assertModelStrictEquals(
                $userLtcsCalcSpec,
                $actual->head()
            );
        });
        $this->should('return stored entity', function (): void {
            $entity = new UserLtcsCalcSpec(
                id: null,
                userId: $this->examples->users[0]->id,
                effectivatedOn: Carbon::now(),
                locationAddition: LtcsUserLocationAddition::none(),
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
            $this->repository->removeById($this->examples->userLtcsCalcSpecs[2]->id, $this->examples->userLtcsCalcSpecs[3]->id);
            $userLtcsCalcSpec0 = $this->repository->lookup($this->examples->userLtcsCalcSpecs[2]->id);
            $this->assertCount(0, $userLtcsCalcSpec0);
            $userLtcsCalcSpec1 = $this->repository->lookup($this->examples->userLtcsCalcSpecs[3]->id);
            $this->assertCount(0, $userLtcsCalcSpec1);
        });
        $this->should('not remove other entities', function (): void {
            $this->repository->removeById($this->examples->userLtcsCalcSpecs[2]->id);
            $userLtcsCalcSpec0 = $this->repository->lookup($this->examples->userLtcsCalcSpecs[2]->id);
            $this->assertCount(0, $userLtcsCalcSpec0);
            $userLtcsCalcSpec1 = $this->repository->lookup($this->examples->userLtcsCalcSpecs[3]->id);
            $userLtcsCalcSpec2 = $this->repository->lookup($this->examples->userLtcsCalcSpecs[1]->id);
            $this->assertCount(1, $userLtcsCalcSpec1);
            $this->assertModelStrictEquals($this->examples->userLtcsCalcSpecs[3], $userLtcsCalcSpec1->head());
            $this->assertCount(1, $userLtcsCalcSpec2);
            $this->assertModelStrictEquals($this->examples->userLtcsCalcSpecs[1], $userLtcsCalcSpec2->head());
        });
    }
}
