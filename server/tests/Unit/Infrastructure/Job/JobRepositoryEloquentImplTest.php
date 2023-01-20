<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Job;

use App\Concretes\PermanentDatabaseTransactionManager;
use Domain\Common\Carbon;
use Domain\Job\Job as DomainJob;
use Domain\Job\JobStatus;
use Infrastructure\Job\JobRepositoryEloquentImpl;
use Lib\Json;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Fixtures\OrganizationFixture;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\DatabaseMixin;
use Tests\Unit\Test;

/**
 * JobRepositoryEloquentImpl のテスト.
 */
class JobRepositoryEloquentImplTest extends Test
{
    use CarbonMixin;
    use DatabaseMixin;
    use ExamplesConsumer;
    use OrganizationFixture;
    use UnitSupport;

    private JobRepositoryEloquentImpl $repository;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (JobRepositoryEloquentImplTest $self): void {
            $self->repository = app(JobRepositoryEloquentImpl::class);
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
    public function describe_lookupOptionByToken(): void
    {
        $this->should('return an entity when the token exists in db', function (): void {
            $expected = $this->examples->jobs[0];
            $actual = $this->repository->lookupOptionByToken($this->examples->jobs[0]->token);

            $this->assertEquals(1, $actual->size());
            $this->assertModelStrictEquals($expected, $actual->head());
        });
        $this->should('return empty seq NotFoundException when the token not exists in db', function (): void {
            $actual = $this->repository->lookupOptionByToken(self::NOT_EXISTING_TOKEN);
            $this->assertCount(0, $actual);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_lookup(): void
    {
        $this->should('return an entity when the id exists in db', function (): void {
            $expected = $this->examples->jobs[0];
            $actual = $this->repository->lookup($this->examples->jobs[0]->id);

            $this->assertEquals(1, $actual->size());
            $this->assertModelStrictEquals($expected, $actual->head());
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
                'id' => self::NOT_EXISTING_ID,
                'organizationId' => $this->examples->organizations[0]->id,
                'staffId' => $this->examples->staffs[0]->id,
                'data' => Json::encode([]),
                'status' => JobStatus::waiting(),
                'token' => self::NOT_EXISTING_TOKEN,
                'createdAt' => Carbon::now(),
                'updatedAt' => Carbon::now(),
            ];
            $entity = DomainJob::create($attrs);
            $stored = $this->repository->store($entity);
            $actual = $this->repository->lookup($stored->id);
            $this->assertCount(1, $actual);
            $this->assertModelStrictEquals(
                $stored,
                $actual->head()
            );
        });
        $this->should('update the entity', function (): void {
            $this->assertNotEquals(Carbon::now(), $this->examples->jobs[0]->createdAt);
            $job = $this->examples->jobs[0]->copy(['status' => JobStatus::inProgress()]);
            $this->repository->store($job);
            $actual = $this->repository->lookup($this->examples->jobs[0]->id);
            $this->assertCount(1, $actual);
            $this->assertModelStrictEquals(
                $job,
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
            $this->repository->removeById($this->examples->jobs[0]->id, $this->examples->jobs[1]->id);
            $job0 = $this->repository->lookup($this->examples->jobs[0]->id);
            $this->assertCount(0, $job0);
            $job1 = $this->repository->lookup($this->examples->jobs[1]->id);
            $this->assertCount(0, $job1);
        });
        $this->should('not remove other entities', function (): void {
            $this->repository->removeById($this->examples->jobs[0]->id);
            $job0 = $this->repository->lookup($this->examples->jobs[0]->id);
            $this->assertCount(0, $job0);
            $job1 = $this->repository->lookup($this->examples->jobs[1]->id);
            $job2 = $this->repository->lookup($this->examples->jobs[2]->id);
            $this->assertCount(1, $job1);
            $this->assertModelStrictEquals($this->examples->jobs[1], $job1->head());
            $this->assertCount(1, $job2);
            $this->assertModelStrictEquals($this->examples->jobs[2], $job2->head());
        });
    }
}
