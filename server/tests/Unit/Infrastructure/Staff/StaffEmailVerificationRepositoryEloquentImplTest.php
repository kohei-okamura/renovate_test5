<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Staff;

use App\Concretes\PermanentDatabaseTransactionManager;
use Domain\Common\Carbon;
use Domain\Staff\StaffEmailVerification;
use Illuminate\Support\Str;
use Infrastructure\Staff\StaffEmailVerificationRepositoryEloquentImpl;
use ScalikePHP\None;
use ScalikePHP\Some;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\DatabaseMixin;
use Tests\Unit\Test;

/**
 * StaffEmailVerificationRepositoryEloquentImpl のテスト.
 */
class StaffEmailVerificationRepositoryEloquentImplTest extends Test
{
    use CarbonMixin;
    use DatabaseMixin;
    use ExamplesConsumer;
    use UnitSupport;

    private StaffEmailVerificationRepositoryEloquentImpl $repository;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (StaffEmailVerificationRepositoryEloquentImplTest $self): void {
            $self->repository = app(StaffEmailVerificationRepositoryEloquentImpl::class);
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
            $actual = $this->repository->lookup($this->examples->staffEmailVerifications[0]->id);
            $this->assertCount(1, $actual);
            $this->assertModelStrictEquals($this->examples->staffEmailVerifications[0], $actual->head());
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
    public function describe_store(): void
    {
        $this->should('add the entity to repository when it does not exist in repository', function (): void {
            $attrs = [
                'id' => 4,
                'staffId' => $this->examples->staffs[0]->id,
                'name' => $this->examples->staffs[0]->name,
                'email' => 'sample@example.com',
                'token' => Str::random(60),
                'expiredAt' => Carbon::create(2019, 1, 1, 1, 1, 1),
                'createdAt' => Carbon::create(2019, 2, 2, 2, 2, 2),
            ];
            $entity = StaffEmailVerification::create($attrs);

            $stored = $this->repository->store($entity);

            $actual = $this->repository->lookup($stored->id);
            $this->assertCount(1, $actual);
            $this->assertModelStrictEquals(
                $stored,
                $actual->head()
            );
        });
        $this->should('update the entity', function (): void {
            $this->assertNotEquals('sample@example.com', $this->examples->staffEmailVerifications[0]->email);
            $staff = $this->examples->staffEmailVerifications[0]->copy(['email' => 'sample@example.com']);
            $this->repository->store($staff);

            $actual = $this->repository->lookup($this->examples->staffEmailVerifications[0]->id);
            $this->assertCount(1, $actual);
            $this->assertModelStrictEquals(
                $staff,
                $actual->head()
            );
        });
        $this->should('return stored entity', function (): void {
            $entity = $this->examples->staffEmailVerifications[0]->copy(['email' => 'sample@example.com']);
            $this->assertNotEquals('sample@example.com', $this->examples->staffEmailVerifications[0]->email);

            $this->assertModelStrictEquals(
                $entity,
                $this->repository->store($entity)
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
            $this->repository->remove($this->examples->staffEmailVerifications[0]);

            $actual = $this->repository->lookup($this->examples->staffEmailVerifications[0]->id);
            $this->assertCount(0, $actual);
        });
        $this->should('not remove other entities', function (): void {
            $this->repository->remove($this->examples->staffEmailVerifications[0]);

            $this->assertTrue($this->repository->lookup($this->examples->staffEmailVerifications[1]->id)->nonEmpty());
            $this->assertTrue($this->repository->lookup($this->examples->staffEmailVerifications[2]->id)->nonEmpty());
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
                $this->examples->staffEmailVerifications[0]->id,
                $this->examples->staffEmailVerifications[1]->id
            );

            $staffEmailVerifications0 = $this->repository->lookup($this->examples->staffEmailVerifications[0]->id);
            $staffEmailVerifications1 = $this->repository->lookup($this->examples->staffEmailVerifications[1]->id);
            $this->assertCount(0, $staffEmailVerifications0);
            $this->assertCount(0, $staffEmailVerifications1);
        });
        $this->should('not remove other entities', function (): void {
            $this->repository->removeById($this->examples->staffEmailVerifications[0]->id);

            $this->assertTrue($this->repository->lookup($this->examples->staffEmailVerifications[1]->id)->nonEmpty());
            $this->assertTrue($this->repository->lookup($this->examples->staffEmailVerifications[2]->id)->nonEmpty());
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_lookupOptionByToken(): void
    {
        $this->should('return some entity when the token exists in db', function (): void {
            $x = $this->repository->lookupOptionByToken($this->examples->staffEmailVerifications[0]->token);
            $this->assertInstanceOf(Some::class, $x);
            $this->assertEquals($this->examples->staffEmailVerifications[0]->id, $x->get()->id);
        });
        $this->should('return None when the token not exists in db', function (): void {
            $x = $this->repository->lookupOptionByToken('INVALID_TOKEN');
            $this->assertInstanceOf(None::class, $x);
        });
    }
}
