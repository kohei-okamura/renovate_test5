<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Billing;

use App\Concretes\PermanentDatabaseTransactionManager;
use Domain\Billing\DwsBilling;
use Domain\Common\Carbon;
use Infrastructure\Billing\DwsBillingRepositoryEloquentImpl;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\DatabaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * DwsBillingRepositoryEloquentImpl のテスト.
 */
class DwsBillingRepositoryEloquentImplTest extends Test
{
    use CarbonMixin;
    use DatabaseMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use UnitSupport;

    private DwsBillingRepositoryEloquentImpl $repository;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (DwsBillingRepositoryEloquentImplTest $self): void {
            $self->repository = app(DwsBillingRepositoryEloquentImpl::class);
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
            $actual = $this->repository->lookup($this->examples->dwsBillings[0]->id);
            $this->assertCount(1, $actual);
            $this->assertModelStrictEquals(
                $this->examples->dwsBillings[0],
                $actual->head()
            );
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
    public function describe_store()
    {
        $this->should('return the entity with id when an entity of not having id stored', function (): void {
            $attrs = [
                'organizationId' => $this->examples->dwsBillings[0]->organizationId,
                'office' => $this->examples->dwsBillings[0]->office,
                'transactedIn' => $this->examples->dwsBillings[0]->transactedIn,
                'files' => [
                    $this->examples->dwsBillings[0]->files[0]->copy(['token' => 'qwertyuiop']),
                ],
                'status' => $this->examples->dwsBillings[0]->status,
                'fixedAt' => $this->examples->dwsBillings[0]->fixedAt,
                'createdAt' => $this->examples->dwsBillings[0]->createdAt,
                'updatedAt' => $this->examples->dwsBillings[0]->updatedAt,
            ];
            $entity = DwsBilling::create($attrs);

            $stored = $this->repository->store($entity);
            $this->assertModelStrictEquals(
                $entity->copy(['id' => $stored->id]),
                $stored
            );
        });
        $this->should('add the entity to repository when it does not exist in repository', function (): void {
            $attrs = [
                'organizationId' => $this->examples->dwsBillings[0]->organizationId,
                'office' => $this->examples->dwsBillings[0]->office,
                'transactedIn' => $this->examples->dwsBillings[0]->transactedIn,
                'files' => [],
                'status' => $this->examples->dwsBillings[0]->status,
                'fixedAt' => $this->examples->dwsBillings[0]->fixedAt,
                'createdAt' => $this->examples->dwsBillings[0]->createdAt,
                'updatedAt' => $this->examples->dwsBillings[0]->updatedAt,
            ];
            $entity = DwsBilling::create($attrs);

            $stored = $this->repository->store($entity);

            $actual = $this->repository->lookup($stored->id);
            $this->assertCount(1, $actual);
            $this->assertModelStrictEquals(
                $stored,
                $actual->head()
            );
        });
        $this->should('update the entity', function (): void {
            $this->assertNotEquals(Carbon::today()->addMonth(), $this->examples->dwsBillings[0]->transactedIn);
            $dwsBilling = $this->examples->dwsBillings[0]->copy(['transactedIn' => Carbon::today()->addMonth()]);
            $this->repository->store($dwsBilling);
            $actual = $this->repository->lookup($this->examples->dwsBillings[0]->id);

            $this->assertCount(1, $actual);
            $this->assertModelStrictEquals(
                $dwsBilling,
                $actual->head()
            );
        });
        $this->should('return stored entity', function (): void {
            $entity = $this->examples->dwsBillings[0]->copy(['transactedIn' => Carbon::today()->addMonth()]);
            $this->assertNotEquals(Carbon::today()->addMonth(), $this->examples->dwsBillings[0]->transactedIn);
            $this->assertModelStrictEquals($entity, $this->repository->store($entity));
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_removeById(): void
    {
        $this->should('remove entities', function (): void {
            $this->repository->removeById($this->examples->dwsBillings[2]->id, $this->examples->dwsBillings[3]->id);

            $dwsBillings0 = $this->repository->lookup($this->examples->dwsBillings[2]->id);
            $dwsBillings1 = $this->repository->lookup($this->examples->dwsBillings[3]->id);
            $this->assertCount(0, $dwsBillings0);
            $this->assertCount(0, $dwsBillings1);
        });
        $this->should('not remove other entities', function (): void {
            $this->repository->removeById($this->examples->dwsBillings[2]->id);

            $actual = $this->repository->lookup($this->examples->dwsBillings[2]->id);
            $this->assertCount(0, $actual);

            $this->assertTrue($this->repository->lookup($this->examples->dwsBillings[1]->id)->nonEmpty());
            $this->assertTrue($this->repository->lookup($this->examples->dwsBillings[3]->id)->nonEmpty());
        });
    }
}
