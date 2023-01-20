<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Infrastructure\User;

use App\Concretes\PermanentDatabaseTransactionManager;
use Domain\Common\Carbon;
use Domain\User\UserLtcsSubsidy;
use Infrastructure\User\UserLtcsSubsidyRepositoryEloquentImpl;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\DatabaseMixin;
use Tests\Unit\Test;

/**
 * SubsidyRepositoryEloquentImpl のテスト
 */
class UserLtcsSubsidyRepositoryEloquentImplTest extends Test
{
    use CarbonMixin;
    use DatabaseMixin;
    use ExamplesConsumer;
    use UnitSupport;

    private UserLtcsSubsidyRepositoryEloquentImpl $repository;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (UserLtcsSubsidyRepositoryEloquentImplTest $self): void {
            $self->repository = app(UserLtcsSubsidyRepositoryEloquentImpl::class);
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
            $actual = $this->repository->lookup($this->examples->userLtcsSubsidies[0]->id);
            $this->assertCount(1, $actual);
            $this->assertModelStrictEquals(
                $this->examples->userLtcsSubsidies[0],
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
                'userId' => $this->examples->users[0]->id,
                'period' => $this->examples->userLtcsSubsidies[0]->period,
                'defrayerCategory' => $this->examples->userLtcsSubsidies[0]->defrayerCategory,
                'defrayerNumber' => $this->examples->userLtcsSubsidies[0]->defrayerNumber,
                'recipientNumber' => $this->examples->userLtcsSubsidies[0]->recipientNumber,
                'benefitRate' => 100,
                'copay' => 0,
                'isEnabled' => true,
                'version' => 1,
                'createdAt' => Carbon::now(),
                'updatedAt' => Carbon::now(),
            ];
            $entity = UserLtcsSubsidy::create($attrs);

            $stored = $this->repository->store($entity);

            $actual = $this->repository->lookup($stored->id);

            $this->assertCount(1, $actual);
            $this->assertModelStrictEquals(
                $stored,
                $actual->head()
            );
        });
        $this->should('update the entity', function (): void {
            $this->assertNotEquals(Carbon::now(), $this->examples->userLtcsSubsidies[0]->createdAt);
            $userLtcsSubsidy = $this->examples->userLtcsSubsidies[0]->copy(['version' => 2]);

            $this->repository->store($userLtcsSubsidy);

            $actual = $this->repository->lookup($this->examples->userLtcsSubsidies[0]->id);

            $this->assertCount(1, $actual);
            $this->assertModelStrictEquals(
                $userLtcsSubsidy,
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
            $this->repository->removeById($this->examples->userLtcsSubsidies[0]->id, $this->examples->userLtcsSubsidies[1]->id);
            $userLtcsSubsidy0 = $this->repository->lookup($this->examples->userLtcsSubsidies[0]->id);
            $this->assertCount(0, $userLtcsSubsidy0);
            $userLtcsSubsidy1 = $this->repository->lookup($this->examples->userLtcsSubsidies[1]->id);
            $this->assertCount(0, $userLtcsSubsidy1);
        });
        $this->should('not remove other entities', function (): void {
            $this->repository->removeById($this->examples->userLtcsSubsidies[0]->id);
            $userLtcsSubsidy0 = $this->repository->lookup($this->examples->userLtcsSubsidies[0]->id);
            $this->assertCount(0, $userLtcsSubsidy0);

            $userLtcsSubsidy1 = $this->repository->lookup($this->examples->userLtcsSubsidies[1]->id);
            $userLtcsSubsidy2 = $this->repository->lookup($this->examples->userLtcsSubsidies[2]->id);
            $this->assertCount(1, $userLtcsSubsidy1);
            $this->assertModelStrictEquals($this->examples->userLtcsSubsidies[1], $userLtcsSubsidy1->head());
            $this->assertCount(1, $userLtcsSubsidy2);
            $this->assertModelStrictEquals($this->examples->userLtcsSubsidies[2], $userLtcsSubsidy2->head());
        });
    }
}
