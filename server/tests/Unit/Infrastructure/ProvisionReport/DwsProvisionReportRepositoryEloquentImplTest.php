<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Infrastructure\ProvisionReport;

use App\Concretes\PermanentDatabaseTransactionManager;
use Domain\ProvisionReport\DwsProvisionReportStatus;
use Infrastructure\ProvisionReport\DwsProvisionReportRepositoryEloquentImpl;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\DatabaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * {@link \Infrastructure\ProvisionReport\DwsProvisionReportRepositoryEloquentImpl} のテスト.
 */
final class DwsProvisionReportRepositoryEloquentImplTest extends Test
{
    use CarbonMixin;
    use DatabaseMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use UnitSupport;

    private DwsProvisionReportRepositoryEloquentImpl $repository;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->repository = app(DwsProvisionReportRepositoryEloquentImpl::class);
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
            $expected = $this->examples->dwsProvisionReports[0];
            $actual = $this->repository->lookup($this->examples->dwsProvisionReports[0]->id);

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
    public function describe_store()
    {
        $this->should('add the entity', function (): void {
            $entity = $this->examples->dwsProvisionReports[0]->copy(['id' => self::NOT_EXISTING_ID]);
            $stored = $this->repository->store($entity);
            $actual = $this->repository->lookup($stored->id);
            $this->assertCount(1, $actual);
            $this->assertModelStrictEquals(
                $entity,
                $actual->head()
            );
        });
        $this->should('update the entity', function (): void {
            $newStatus = DwsProvisionReportStatus::fixed();
            $this->assertNotEquals($newStatus, $this->examples->dwsProvisionReports[0]->status);
            $dwsProvisionReport = $this->examples->dwsProvisionReports[0]->copy(['status' => $newStatus]);
            $this->repository->store($dwsProvisionReport);
            $actual = $this->repository->lookup($this->examples->dwsProvisionReports[0]->id);

            $this->assertCount(1, $actual);
            $this->assertModelStrictEquals(
                $dwsProvisionReport,
                $actual->head()
            );
        });
        $this->should('return stored entity', function (): void {
            $entity = $this->examples->dwsProvisionReports[0]->copy(['id' => self::NOT_EXISTING_ID]);

            $stored = $this->repository->store($entity);
            $this->assertModelStrictEquals(
                $entity,
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
            $this->repository->removeById(
                $this->examples->dwsProvisionReports[2]->id,
                $this->examples->dwsProvisionReports[3]->id
            );
            $report0 = $this->repository->lookup($this->examples->dwsProvisionReports[2]->id);
            $this->assertCount(0, $report0);
            $report1 = $this->repository->lookup($this->examples->dwsProvisionReports[3]->id);
            $this->assertCount(0, $report1);
        });
        $this->should('not remove other entities', function (): void {
            $this->repository->removeById($this->examples->dwsProvisionReports[0]->id);
            $report0 = $this->repository->lookup($this->examples->dwsProvisionReports[0]->id);
            $this->assertCount(0, $report0);
            $report1 = $this->repository->lookup($this->examples->dwsProvisionReports[1]->id);
            $report2 = $this->repository->lookup($this->examples->dwsProvisionReports[2]->id);
            $this->assertCount(1, $report1);
            $this->assertModelStrictEquals($this->examples->dwsProvisionReports[1], $report1->head());
            $this->assertCount(1, $report2);
            $this->assertModelStrictEquals($this->examples->dwsProvisionReports[2], $report2->head());
        });
    }
}
