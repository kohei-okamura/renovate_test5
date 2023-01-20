<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Calling;

use App\Concretes\PermanentDatabaseTransactionManager;
use Domain\Calling\Calling as DomainCalling;
use Domain\Common\Carbon;
use Illuminate\Support\Str;
use Infrastructure\Calling\CallingRepositoryEloquentImpl;
use ScalikePHP\None;
use ScalikePHP\Some;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\DatabaseMixin;
use Tests\Unit\Test;

/**
 * CallingRepositoryEloquentImpl のテスト.
 */
class CallingRepositoryEloquentImplTest extends Test
{
    use CarbonMixin;
    use DatabaseMixin;
    use ExamplesConsumer;
    use UnitSupport;

    private CallingRepositoryEloquentImpl $repository;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (CallingRepositoryEloquentImplTest $self): void {
            $self->repository = app(CallingRepositoryEloquentImpl::class);
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
            $expected = $this->examples->callings[0];
            $actual = $this->repository->lookup($this->examples->callings[0]->id);
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
                'staffId' => $this->examples->staffs[0]->id,
                'shiftIds' => [
                    $this->examples->shifts[0]->id,
                    $this->examples->shifts[1]->id,
                ],
                'token' => Str::random(60),
                'expiredAt' => Carbon::now(),
                'createdAt' => Carbon::now(),
            ];
            $entity = DomainCalling::create($attrs);
            $stored = $this->repository->store($entity);
            $actual = $this->repository->lookup($stored->id);
            $this->assertCount(1, $actual);
            $this->assertModelStrictEquals(
                $stored,
                $actual->head()
            );
        });
        $this->should('update the entity', function (): void {
            $this->assertNotEquals(Carbon::now(), $this->examples->callings[0]);
            $calling = $this->examples->callings[0]->copy();
            $this->repository->store($calling);
            $actual = $this->repository->lookup($this->examples->callings[0]->id);
            $this->assertCount(1, $actual);
            $this->assertModelStrictEquals(
                $calling,
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
            $calling = $this->examples->callings[0];
            $this->repository->remove($calling);
            $actual = $this->repository->lookup($this->examples->callings[0]->id);
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
            $this->repository->removeById($this->examples->callings[0]->id, $this->examples->callings[1]->id);
            $calling0 = $this->repository->lookup($this->examples->callings[0]->id);
            $this->assertCount(0, $calling0);
            $calling1 = $this->repository->lookup($this->examples->callings[1]->id);
            $this->assertCount(0, $calling1);
        });
        $this->should('not remove other entities', function (): void {
            $this->repository->removeById($this->examples->callings[0]->id);
            $calling0 = $this->repository->lookup($this->examples->callings[0]->id);
            $this->assertCount(0, $calling0);
            $calling1 = $this->repository->lookup($this->examples->callings[1]->id);
            $calling2 = $this->repository->lookup($this->examples->callings[2]->id);
            $this->assertCount(1, $calling1);
            $this->assertModelStrictEquals($this->examples->callings[1], $calling1->head());
            $this->assertCount(1, $calling2);
            $this->assertModelStrictEquals($this->examples->callings[2], $calling2->head());
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_lookupOptionByToken(): void
    {
        $this->should('return some entity when the token exists in db', function (): void {
            $x = $this->repository->lookupOptionByToken($this->examples->callings[0]->token);

            $this->assertInstanceOf(Some::class, $x);
            $this->assertEquals($this->examples->callings[0]->id, $x->get()->id);
        });
        $this->should('return None when the token not exists in db', function (): void {
            $x = $this->repository->lookupOptionByToken('INVALID_TOKEN');

            $this->assertInstanceOf(None::class, $x);
        });
    }
}
