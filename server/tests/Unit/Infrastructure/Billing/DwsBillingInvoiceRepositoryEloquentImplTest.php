<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Billing;

use App\Concretes\PermanentDatabaseTransactionManager;
use Domain\Billing\DwsBillingInvoice;
use Domain\Billing\DwsBillingPaymentCategory;
use Domain\Billing\DwsServiceDivisionCode;
use Infrastructure\Billing\DwsBillingInvoiceRepositoryEloquentImpl;
use ScalikePHP\Map;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\DatabaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * DwsBillingInvoiceRepositoryEloquentImpl のテスト.
 */
class DwsBillingInvoiceRepositoryEloquentImplTest extends Test
{
    use CarbonMixin;
    use DatabaseMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use UnitSupport;

    private DwsBillingInvoiceRepositoryEloquentImpl $repository;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (DwsBillingInvoiceRepositoryEloquentImplTest $self): void {
            $self->repository = app(DwsBillingInvoiceRepositoryEloquentImpl::class);
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
            $actual = $this->repository->lookup($this->examples->dwsBillingInvoices[0]->id);
            $this->assertCount(1, $actual);
            $this->assertModelStrictEquals(
                $this->examples->dwsBillingInvoices[0],
                $actual->head()
            );
        });
        $this->should('return empty seq NotFoundException when the id not exists in db', function (): void {
            $actual = $this->repository->lookup(self::NOT_EXISTING_ID);
            $this->assertCount(0, $actual);
        });
        $this->should('return an entity when entity has multiple DwsBillingInvoiceItem', function (): void {
            $dwsBillingInvoice = $this->examples->dwsBillingInvoices[0]->copy([
                'items' => [
                    DwsBillingInvoice::item([
                        'paymentCategory' => DwsBillingPaymentCategory::category1(),
                        'serviceDivisionCode' => DwsServiceDivisionCode::visitingCareForPwsd(),
                        'subtotalCount' => 20,
                        'subtotalScore' => 10000,
                        'subtotalFee' => 1000000,
                        'subtotalBenefit' => 1000000,
                        'subtotalCopay' => 37200,
                        'subtotalSubsidy' => 100000,
                    ]),
                    DwsBillingInvoice::item([
                        'paymentCategory' => DwsBillingPaymentCategory::category1(),
                        'serviceDivisionCode' => DwsServiceDivisionCode::homeHelpService(),
                        'subtotalCount' => 20,
                        'subtotalScore' => 10000,
                        'subtotalFee' => 1000000,
                        'subtotalBenefit' => 1000000,
                        'subtotalCopay' => 37200,
                        'subtotalSubsidy' => 100000,
                    ]),
                ],
            ]);
            $this->repository->store($dwsBillingInvoice);
            $actual = $this->repository->lookup($this->examples->dwsBillingInvoices[0]->id);
            $this->assertModelStrictEquals(
                $dwsBillingInvoice,
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
                'dwsBillingBundleId' => $this->examples->dwsBillingInvoices[0]->dwsBillingBundleId,
                'claimAmount' => $this->examples->dwsBillingInvoices[0]->claimAmount,
                'dwsPayment' => $this->examples->dwsBillingInvoices[0]->dwsPayment,
                'highCostDwsPayment' => $this->examples->dwsBillingInvoices[0]->highCostDwsPayment,
                'totalCount' => $this->examples->dwsBillingInvoices[0]->totalCount,
                'totalScore' => $this->examples->dwsBillingInvoices[0]->totalScore,
                'totalFee' => $this->examples->dwsBillingInvoices[0]->totalFee,
                'totalBenefit' => $this->examples->dwsBillingInvoices[0]->totalBenefit,
                'totalCopay' => $this->examples->dwsBillingInvoices[0]->totalCopay,
                'totalSubsidy' => $this->examples->dwsBillingInvoices[0]->totalSubsidy,
                'items' => $this->examples->dwsBillingInvoices[0]->items,
                'createdAt' => $this->examples->dwsBillingInvoices[0]->createdAt,
                'updatedAt' => $this->examples->dwsBillingInvoices[0]->updatedAt,
            ];
            $entity = DwsBillingInvoice::create($attrs);

            $stored = $this->repository->store($entity);
            $this->assertModelStrictEquals(
                $entity->copy(['id' => $stored->id]),
                $stored
            );
        });
        $this->should('add the entity to repository when it does not exist in repository', function (): void {
            $attrs = [
                'dwsBillingBundleId' => $this->examples->dwsBillingInvoices[0]->dwsBillingBundleId,
                'claimAmount' => $this->examples->dwsBillingInvoices[0]->claimAmount,
                'dwsPayment' => $this->examples->dwsBillingInvoices[0]->dwsPayment,
                'highCostDwsPayment' => $this->examples->dwsBillingInvoices[0]->highCostDwsPayment,
                'totalCount' => $this->examples->dwsBillingInvoices[0]->totalCount,
                'totalScore' => $this->examples->dwsBillingInvoices[0]->totalScore,
                'totalFee' => $this->examples->dwsBillingInvoices[0]->totalFee,
                'totalBenefit' => $this->examples->dwsBillingInvoices[0]->totalBenefit,
                'totalCopay' => $this->examples->dwsBillingInvoices[0]->totalCopay,
                'totalSubsidy' => $this->examples->dwsBillingInvoices[0]->totalSubsidy,
                'items' => $this->examples->dwsBillingInvoices[0]->items,
                'createdAt' => $this->examples->dwsBillingInvoices[0]->createdAt,
                'updatedAt' => $this->examples->dwsBillingInvoices[0]->updatedAt,
            ];
            $entity = DwsBillingInvoice::create($attrs);

            $stored = $this->repository->store($entity);

            $actual = $this->repository->lookup($stored->id);
            $this->assertCount(1, $actual);
            $this->assertModelStrictEquals(
                $stored,
                $actual->head()
            );
        });
        $this->should('update the entity', function (): void {
            $this->assertNotEquals(500, $this->examples->dwsBillingInvoices[0]->totalSubsidy);
            $dwsBillingInvoice = $this->examples->dwsBillingInvoices[0]->copy(['totalSubsidy' => 500]);
            $this->repository->store($dwsBillingInvoice);
            $actual = $this->repository->lookup($this->examples->dwsBillingInvoices[0]->id);

            $this->assertCount(1, $actual);
            $this->assertModelStrictEquals(
                $dwsBillingInvoice,
                $actual->head()
            );
        });
        $this->should('return stored entity', function (): void {
            $entity = $this->examples->dwsBillingInvoices[0]->copy(['totalSubsidy' => 500]);
            $this->assertNotEquals(500, $this->examples->dwsBillingInvoices[0]->totalSubsidy);
            $this->assertModelStrictEquals($entity, $this->repository->store($entity));
        });
        $this->should('delete and insert DomainDwsBillingInvoiceItem when update the entity', function (): void {
            $this->assertCount(2, $this->examples->dwsBillingInvoices[3]->items);
            $dwsBillingInvoices = $this->examples->dwsBillingInvoices[3]->copy(['items' => $this->examples->dwsBillingInvoices[0]->items]);
            $this->repository->store($dwsBillingInvoices);

            /** @var DwsBillingInvoice $actual */
            $actual = $this->repository->lookup($dwsBillingInvoices->id)->head();
            $this->assertCount(1, $actual->items);
            $this->assertEach(
                function ($a, $b): void {
                    $this->assertModelStrictEquals($a, $b);
                },
                $dwsBillingInvoices->items,
                $actual->items,
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
            $this->repository->removeById($this->examples->dwsBillingInvoices[2]->id, $this->examples->dwsBillingInvoices[3]->id);

            $dwsBillingInvoices0 = $this->repository->lookup($this->examples->dwsBillingInvoices[2]->id);
            $dwsBillingInvoices1 = $this->repository->lookup($this->examples->dwsBillingInvoices[3]->id);
            $this->assertCount(0, $dwsBillingInvoices0);
            $this->assertCount(0, $dwsBillingInvoices1);
        });
        $this->should('not remove other entities', function (): void {
            $this->repository->removeById($this->examples->dwsBillingInvoices[2]->id);

            $actual = $this->repository->lookup($this->examples->dwsBillingInvoices[2]->id);
            $this->assertCount(0, $actual);

            $this->assertTrue($this->repository->lookup($this->examples->dwsBillingInvoices[1]->id)->nonEmpty());
            $this->assertTrue($this->repository->lookup($this->examples->dwsBillingInvoices[3]->id)->nonEmpty());
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
                $this->assertForAll($x, fn (DwsBillingInvoice $bundle): bool => $bundle->dwsBillingBundleId === $key);
            });
        });
    }
}
