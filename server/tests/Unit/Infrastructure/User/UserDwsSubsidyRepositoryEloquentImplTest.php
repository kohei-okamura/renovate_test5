<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Infrastructure\User;

use Domain\Common\Carbon;
use Infrastructure\User\UserDwsSubsidyRepositoryEloquentImpl;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\DatabaseMixin;
use Tests\Unit\Test;

/**
 * {@link \Infrastructure\User\UserDwsSubsidyRepositoryEloquentImpl} Test.
 */
class UserDwsSubsidyRepositoryEloquentImplTest extends Test
{
    use ExamplesConsumer;
    use DatabaseMixin;
    use UnitSupport;

    private UserDwsSubsidyRepositoryEloquentImpl $repository;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (UserDwsSubsidyRepositoryEloquentImplTest $self): void {
            $self->repository = app(UserDwsSubsidyRepositoryEloquentImpl::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_lookup(): void
    {
        $this->should('return an entity when the id exists in db', function (): void {
            $actual = $this->repository->lookup($this->examples->userDwsSubsidies[0]->id);
            $this->assertCount(1, $actual);
            $this->assertModelStrictEquals(
                $this->examples->userDwsSubsidies[0],
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
            $entity = $this->examples->userDwsSubsidies[0]->copy(['id' => null]);
            $stored = $this->repository->store($entity);
            $expect = $this->repository->lookup($stored->id);

            $this->assertModelStrictEquals(
                $expect->head()->copy(['id' => null]), // IDは乗っていないため
                $entity
            );
        });
        $this->should('return stored entity', function (): void {
            $entity = $this->examples->userDwsSubsidies[1]->copy(['id' => null]);
            $stored = $this->repository->store($entity);
            $expect = $this->repository->lookup($stored->id);

            $this->assertModelStrictEquals(
                $expect->head(),
                $stored
            );
        });
        $this->should('update the entity', function (): void {
            $this->assertNotEquals(Carbon::now(), $this->examples->userDwsSubsidies[0]->createdAt);
            $userDwsSubsidy = $this->examples->userDwsSubsidies[0]->copy(['version' => 2]);

            $this->repository->store($userDwsSubsidy);

            $actual = $this->repository->lookup($this->examples->userDwsSubsidies[0]->id);

            $this->assertCount(1, $actual);
            $this->assertModelStrictEquals(
                $userDwsSubsidy,
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
            $this->repository->removeById($this->examples->userDwsSubsidies[0]->id, $this->examples->userDwsSubsidies[1]->id);
            $userDwsSubsidy0 = $this->repository->lookup($this->examples->userDwsSubsidies[0]->id);
            $this->assertCount(0, $userDwsSubsidy0);
            $userDwsSubsidy1 = $this->repository->lookup($this->examples->userDwsSubsidies[1]->id);
            $this->assertCount(0, $userDwsSubsidy1);
        });
        $this->should('not remove other entities', function (): void {
            $this->repository->removeById($this->examples->userLtcsSubsidies[0]->id);
            $userDwsSubsidy0 = $this->repository->lookup($this->examples->userDwsSubsidies[0]->id);
            $this->assertCount(0, $userDwsSubsidy0);

            $userDwsSubsidy1 = $this->repository->lookup($this->examples->userDwsSubsidies[1]->id);
            $userDwsSubsidy2 = $this->repository->lookup($this->examples->userDwsSubsidies[2]->id);
            $this->assertCount(1, $userDwsSubsidy1);
            $this->assertModelStrictEquals($this->examples->userDwsSubsidies[1], $userDwsSubsidy1->head());
            $this->assertCount(1, $userDwsSubsidy2);
            $this->assertModelStrictEquals($this->examples->userDwsSubsidies[2], $userDwsSubsidy2->head());
        });
    }
}
