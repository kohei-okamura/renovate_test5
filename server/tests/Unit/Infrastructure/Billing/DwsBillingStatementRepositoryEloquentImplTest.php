<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Billing;

use App\Concretes\PermanentDatabaseTransactionManager;
use Domain\Billing\DwsBillingStatement;
use Domain\Billing\DwsBillingStatementAggregate;
use Domain\Billing\DwsServiceDivisionCode;
use Domain\Common\Carbon;
use Domain\Common\Decimal;
use Infrastructure\Billing\DwsBillingStatementRepositoryEloquentImpl;
use ScalikePHP\Map;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\DatabaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * DwsBillingStatementRepositoryEloquentImpl のテスト.
 */
class DwsBillingStatementRepositoryEloquentImplTest extends Test
{
    use CarbonMixin;
    use DatabaseMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use UnitSupport;

    private DwsBillingStatementRepositoryEloquentImpl $repository;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (DwsBillingStatementRepositoryEloquentImplTest $self): void {
            $self->repository = app(DwsBillingStatementRepositoryEloquentImpl::class);
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
            $actual = $this->repository->lookup($this->examples->dwsBillingStatements[0]->id);
            $this->assertCount(1, $actual);
            $this->assertModelStrictEquals(
                $this->examples->dwsBillingStatements[0],
                $actual->head()
            );
        });
        $this->should('return empty seq NotFoundException when the id not exists in db', function (): void {
            $actual = $this->repository->lookup(self::NOT_EXISTING_ID);
            $this->assertCount(0, $actual);
        });
        $this->should('return an entity when entity has multiple DwsBillingStatementAggregate', function (): void {
            $dwsBillingStatement = $this->examples->dwsBillingStatements[0]->copy([
                'aggregates' => [
                    new DwsBillingStatementAggregate(
                        serviceDivisionCode: DwsServiceDivisionCode::homeHelpService(),
                        startedOn: Carbon::today()->subDay(),
                        terminatedOn: Carbon::today(),
                        serviceDays: 1,
                        subtotalScore: 10000,
                        unitCost: Decimal::fromInt(10_0000),
                        subtotalFee: 10000,
                        unmanagedCopay: 10000,
                        managedCopay: 10000,
                        cappedCopay: 10000,
                        adjustedCopay: 10000,
                        coordinatedCopay: 10000,
                        subtotalCopay: 10000,
                        subtotalBenefit: 10000,
                        subtotalSubsidy: 10000,
                    ),
                    new DwsBillingStatementAggregate(
                        serviceDivisionCode: DwsServiceDivisionCode::visitingCareForPwsd(),
                        startedOn: Carbon::today()->subDay(),
                        terminatedOn: Carbon::today(),
                        serviceDays: 1,
                        subtotalScore: 10000,
                        unitCost: Decimal::fromInt(10_0000),
                        subtotalFee: 10000,
                        unmanagedCopay: 10000,
                        managedCopay: 10000,
                        cappedCopay: 10000,
                        adjustedCopay: 10000,
                        coordinatedCopay: 10000,
                        subtotalCopay: 10000,
                        subtotalBenefit: 10000,
                        subtotalSubsidy: 10000,
                    ),
                ],
            ]);
            $this->repository->store($dwsBillingStatement);
            $actual = $this->repository->lookup($this->examples->dwsBillingStatements[0]->id);
            $this->assertModelStrictEquals(
                $dwsBillingStatement,
                $actual->head()
            );
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
                'dwsBillingId' => $this->examples->dwsBillingStatements[0]->dwsBillingId,
                'dwsBillingBundleId' => $this->examples->dwsBillingStatements[0]->dwsBillingBundleId,
                'subsidyCityCode' => $this->examples->dwsBillingStatements[0]->subsidyCityCode,
                'user' => $this->examples->dwsBillingStatements[0]->user,
                'dwsAreaGradeCode' => $this->examples->dwsBillingStatements[0]->dwsAreaGradeCode,
                'dwsAreaGradeName' => $this->examples->dwsBillingStatements[0]->dwsAreaGradeName,
                'copayLimit' => $this->examples->dwsBillingStatements[0]->copayLimit,
                'totalScore' => $this->examples->dwsBillingStatements[0]->totalScore,
                'totalFee' => $this->examples->dwsBillingStatements[0]->totalFee,
                'totalCappedCopay' => $this->examples->dwsBillingStatements[0]->totalCappedCopay,
                'totalAdjustedCopay' => $this->examples->dwsBillingStatements[0]->totalAdjustedCopay,
                'totalCoordinatedCopay' => $this->examples->dwsBillingStatements[0]->totalCoordinatedCopay,
                'totalCopay' => $this->examples->dwsBillingStatements[0]->totalCopay,
                'totalBenefit' => $this->examples->dwsBillingStatements[0]->totalBenefit,
                'totalSubsidy' => $this->examples->dwsBillingStatements[0]->totalSubsidy,
                'isProvided' => $this->examples->dwsBillingStatements[0]->isProvided,
                'copayCoordinationStatus' => $this->examples->dwsBillingStatements[0]->copayCoordinationStatus,
                'copayCoordination' => $this->examples->dwsBillingStatements[0]->copayCoordination,
                'aggregates' => $this->examples->dwsBillingStatements[0]->aggregates,
                'contracts' => $this->examples->dwsBillingStatements[0]->contracts,
                'items' => $this->examples->dwsBillingStatements[0]->items,
                'status' => $this->examples->dwsBillingStatements[0]->status,
                'fixedAt' => $this->examples->dwsBillingStatements[0]->fixedAt,
                'createdAt' => $this->examples->dwsBillingStatements[0]->createdAt,
                'updatedAt' => $this->examples->dwsBillingStatements[0]->updatedAt,
            ];
            $entity = DwsBillingStatement::create($attrs);

            $stored = $this->repository->store($entity);
            $this->assertModelStrictEquals(
                $entity->copy(['id' => $stored->id]),
                $stored
            );
        });
        $this->should('add the entity to repository when it does not exist in repository', function (): void {
            $attrs = [
                'dwsBillingId' => $this->examples->dwsBillingStatements[0]->dwsBillingId,
                'dwsBillingBundleId' => $this->examples->dwsBillingStatements[0]->dwsBillingBundleId,
                'subsidyCityCode' => $this->examples->dwsBillingStatements[0]->subsidyCityCode,
                'user' => $this->examples->dwsBillingStatements[0]->user,
                'dwsAreaGradeCode' => $this->examples->dwsBillingStatements[0]->dwsAreaGradeCode,
                'dwsAreaGradeName' => $this->examples->dwsBillingStatements[0]->dwsAreaGradeName,
                'copayLimit' => $this->examples->dwsBillingStatements[0]->copayLimit,
                'totalScore' => $this->examples->dwsBillingStatements[0]->totalScore,
                'totalFee' => $this->examples->dwsBillingStatements[0]->totalFee,
                'totalCappedCopay' => $this->examples->dwsBillingStatements[0]->totalCappedCopay,
                'totalAdjustedCopay' => $this->examples->dwsBillingStatements[0]->totalAdjustedCopay,
                'totalCoordinatedCopay' => $this->examples->dwsBillingStatements[0]->totalCoordinatedCopay,
                'totalCopay' => $this->examples->dwsBillingStatements[0]->totalCopay,
                'totalBenefit' => $this->examples->dwsBillingStatements[0]->totalBenefit,
                'totalSubsidy' => $this->examples->dwsBillingStatements[0]->totalSubsidy,
                'isProvided' => $this->examples->dwsBillingStatements[0]->isProvided,
                'copayCoordinationStatus' => $this->examples->dwsBillingStatements[0]->copayCoordinationStatus,
                'copayCoordination' => $this->examples->dwsBillingStatements[0]->copayCoordination,
                'aggregates' => $this->examples->dwsBillingStatements[0]->aggregates,
                'contracts' => $this->examples->dwsBillingStatements[0]->contracts,
                'items' => $this->examples->dwsBillingStatements[0]->items,
                'status' => $this->examples->dwsBillingStatements[0]->status,
                'fixedAt' => $this->examples->dwsBillingStatements[0]->fixedAt,
                'createdAt' => $this->examples->dwsBillingStatements[0]->createdAt,
                'updatedAt' => $this->examples->dwsBillingStatements[0]->updatedAt,
            ];
            $entity = DwsBillingStatement::create($attrs);

            $stored = $this->repository->store($entity);

            $actual = $this->repository->lookup($stored->id);
            $this->assertCount(1, $actual);
            $this->assertModelStrictEquals(
                $stored,
                $actual->head()
            );
        });
        $this->should('update the entity', function (): void {
            $this->assertNotEquals(500, $this->examples->dwsBillingStatements[0]->totalBenefit);
            $dwsBillingStatement = $this->examples->dwsBillingStatements[0]->copy(['totalBenefit' => 500]);
            $this->repository->store($dwsBillingStatement);
            $actual = $this->repository->lookup($this->examples->dwsBillingStatements[0]->id);

            $this->assertCount(1, $actual);
            $this->assertModelStrictEquals(
                $dwsBillingStatement,
                $actual->head()
            );
        });
        $this->should('return stored entity', function (): void {
            $entity = $this->examples->dwsBillingStatements[0]->copy(['totalBenefit' => 500]);
            $this->assertNotEquals(500, $this->examples->dwsBillingStatements[0]->totalBenefit);
            $this->assertModelStrictEquals($entity, $this->repository->store($entity));
        });
        $this->should('delete and insert DomainDwsBillingStatementAggregate when update the entity', function (): void {
            $this->assertCount(2, $this->examples->dwsBillingStatements[4]->aggregates);
            $dwsBillingStatement = $this->examples->dwsBillingStatements[4]->copy(['aggregates' => $this->examples->dwsBillingStatements[0]->aggregates]);
            $this->repository->store($dwsBillingStatement);

            /** @var DwsBillingStatement $actual */
            $actual = $this->repository->lookup($dwsBillingStatement->id)->head();
            $this->assertCount(1, $actual->aggregates);
            $this->assertEach(
                function ($a, $b): void {
                    $this->assertModelStrictEquals($a, $b);
                },
                $dwsBillingStatement->aggregates,
                $actual->aggregates,
            );
        });
        $this->should('store normally with entity which is CopayCoordination is null', function (): void {
            $entity = $this->examples->dwsBillingStatements[1]->copy([
                'id' => null,
                'copayCoordination' => null,
            ]);
            /** @var \Domain\Billing\DwsBillingStatement $stored */
            $stored = $this->repository->store($entity);
            $this->assertGreaterThan(0, $stored->id);
            $this->assertNull($stored->copayCoordination);
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
                $this->examples->dwsBillingStatements[2]->id,
                $this->examples->dwsBillingStatements[3]->id
            );

            $dwsBillingStatements0 = $this->repository->lookup($this->examples->dwsBillingStatements[2]->id);
            $dwsBillingStatements1 = $this->repository->lookup($this->examples->dwsBillingStatements[3]->id);
            $this->assertCount(0, $dwsBillingStatements0);
            $this->assertCount(0, $dwsBillingStatements1);
        });
        $this->should('not remove other entities', function (): void {
            $this->repository->removeById($this->examples->dwsBillingStatements[2]->id);

            $actual = $this->repository->lookup($this->examples->dwsBillingStatements[2]->id);
            $this->assertCount(0, $actual);

            $this->assertTrue($this->repository->lookup($this->examples->dwsBillingStatements[1]->id)->nonEmpty());
            $this->assertTrue($this->repository->lookup($this->examples->dwsBillingStatements[3]->id)->nonEmpty());
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
                $this->assertForAll($x, fn (DwsBillingStatement $bundle): bool => $bundle->dwsBillingBundleId === $key);
            });
        });
    }
}
