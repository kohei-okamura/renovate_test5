<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Billing;

use App\Concretes\PermanentDatabaseTransactionManager;
use Domain\Billing\DwsBillingServiceReport;
use Domain\Billing\DwsBillingStatus;
use Domain\Common\Carbon;
use Infrastructure\Billing\DwsBillingServiceReportRepositoryEloquentImpl;
use ScalikePHP\Map;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\DatabaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * {@link \Infrastructure\Billing\DwsBillingServiceReportRepositoryEloquentImpl} のテスト.
 */
final class DwsBillingServiceReportRepositoryEloquentImplTest extends Test
{
    use CarbonMixin;
    use DatabaseMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use UnitSupport;

    private DwsBillingServiceReportRepositoryEloquentImpl $repository;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->repository = app(DwsBillingServiceReportRepositoryEloquentImpl::class);
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
            $actual = $this->repository->lookup($this->examples->dwsBillingServiceReports[0]->id);
            $this->assertCount(1, $actual);
            $this->assertModelStrictEquals(
                $this->examples->dwsBillingServiceReports[0],
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
            $report = $this->examples->dwsBillingServiceReports[0];
            $attrs = [
                'dwsBillingId' => $report->dwsBillingId,
                'dwsBillingBundleId' => $report->dwsBillingBundleId,
                'user' => $report->user,
                'format' => $report->format,
                'plan' => $report->plan,
                'result' => $report->result,
                'emergencyCount' => $report->emergencyCount,
                'firstTimeCount' => $report->firstTimeCount,
                'welfareSpecialistCooperationCount' => $report->welfareSpecialistCooperationCount,
                'behavioralDisorderSupportCooperationCount' => $report->behavioralDisorderSupportCooperationCount,
                'movingCareSupportCount' => $report->movingCareSupportCount,
                'items' => $report->items,
                'status' => $report->status,
                'fixedAt' => $report->fixedAt,
                'createdAt' => $report->createdAt,
                'updatedAt' => $report->updatedAt,
            ];
            $entity = DwsBillingServiceReport::create($attrs);

            $actual = $this->repository->store($entity);

            $expected = $entity->copy(['id' => $actual->id]);
            $this->assertModelStrictEquals($expected, $actual);
        });
        $this->should('add the entity to repository when it does not exist in repository', function (): void {
            $report = $this->examples->dwsBillingServiceReports[0];
            $attrs = [
                'dwsBillingId' => $report->dwsBillingId,
                'dwsBillingBundleId' => $report->dwsBillingBundleId,
                'user' => $report->user,
                'format' => $report->format,
                'plan' => $report->plan,
                'result' => $report->result,
                'emergencyCount' => $report->emergencyCount,
                'firstTimeCount' => $report->firstTimeCount,
                'welfareSpecialistCooperationCount' => $report->welfareSpecialistCooperationCount,
                'behavioralDisorderSupportCooperationCount' => $report->behavioralDisorderSupportCooperationCount,
                'movingCareSupportCount' => $report->movingCareSupportCount,
                'items' => $report->items,
                'status' => $report->status,
                'fixedAt' => $report->fixedAt,
                'createdAt' => $report->createdAt,
                'updatedAt' => $report->updatedAt,
            ];
            $entity = DwsBillingServiceReport::create($attrs);

            $stored = $this->repository->store($entity);
            $actual = $this->repository->lookup($stored->id);

            $this->assertCount(1, $actual);
            $this->assertModelStrictEquals($stored, $actual->head());
        });
        $this->should('add the entity which plan of item is null to repository', function (): void {
            $entity = $this->examples->dwsBillingServiceReports[0]->copy([
                'id' => null,
                'items' => [$this->examples->dwsBillingServiceReports[0]->items[0]->copy(['plan' => null])],
            ]);

            $stored = $this->repository->store($entity);
            $actual = $this->repository->lookup($stored->id);

            $this->assertCount(1, $actual);
            $this->assertModelStrictEquals($stored, $actual->head());
        });
        $this->should('add the entity which result of item is null to repository', function (): void {
            $entity = $this->examples->dwsBillingServiceReports[0]->copy([
                'id' => null,
                'items' => [$this->examples->dwsBillingServiceReports[0]->items[0]->copy(['result' => null])],
            ]);

            $stored = $this->repository->store($entity);
            $actual = $this->repository->lookup($stored->id);

            $this->assertCount(1, $actual);
            $this->assertModelStrictEquals($stored, $actual->head());
        });
        $this->should('update the entity', function (): void {
            $report = $this->examples->dwsBillingServiceReports[0];
            $items = $this->examples->dwsBillingServiceReports[4]->items;
            $status = DwsBillingStatus::fixed();
            $fixedAt = Carbon::now();
            $this->assertNotEquals($fixedAt, $report->fixedAt);
            $expected = $report->copy(compact('items', 'status', 'fixedAt'));

            $this->repository->store($expected);
            $actual = $this->repository->lookup($report->id);

            $this->assertCount(1, $actual);
            $this->assertModelStrictEquals($expected, $actual->head());
        });
        $this->should('return stored entity', function (): void {
            $report = $this->examples->dwsBillingServiceReports[0];
            $status = DwsBillingStatus::fixed();
            $fixedAt = Carbon::now();
            $this->assertNotEquals($fixedAt, $report->fixedAt);
            $expected = $report->copy(compact('status', 'fixedAt'));

            $actual = $this->repository->store($expected);

            $this->assertModelStrictEquals($expected, $actual);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_removeById(): void
    {
        $this->should('remove entities', function (): void {
            $ids = [
                $this->examples->dwsBillingServiceReports[2]->id,
                $this->examples->dwsBillingServiceReports[3]->id,
            ];

            $this->repository->removeById(...$ids);

            $this->assertCount(0, $this->repository->lookup(...$ids));
        });
        $this->should('not remove other entities', function (): void {
            $ids = [
                $this->examples->dwsBillingServiceReports[1]->id,
                $this->examples->dwsBillingServiceReports[2]->id,
                $this->examples->dwsBillingServiceReports[3]->id,
            ];

            $this->repository->removeById($ids[1]);

            $this->assertCount(1, $this->repository->lookup($ids[0]));
            $this->assertCount(0, $this->repository->lookup($ids[1]));
            $this->assertCount(1, $this->repository->lookup($ids[2]));
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_lookupByBundleId(): void
    {
        $this->should('return Map of Seq with Bundle ID of key', function () {
            $ids = [
                $this->examples->dwsBillingBundles[0]->id,
                $this->examples->dwsBillingBundles[1]->id,
            ];
            $actual = $this->repository->lookupByBundleId(...$ids);

            $this->assertInstanceOf(Map::class, $actual);
            $actual->each(function (Seq $x, int $key) use ($ids): void {
                $this->assertTrue(in_array($key, $ids, true));
                $this->assertForAll(
                    $x,
                    fn (DwsBillingServiceReport $serviceReport): bool => $serviceReport->dwsBillingBundleId === $key
                );
            });
        });
    }
}
