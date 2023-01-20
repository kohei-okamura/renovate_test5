<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\Billing;

use Domain\Billing\DwsBillingHighCostPayment;
use Domain\Billing\DwsBillingInvoice;
use Domain\Billing\DwsBillingInvoiceItem;
use Domain\Billing\DwsBillingPayment;
use Domain\Billing\DwsBillingPaymentCategory;
use Illuminate\Support\Arr;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Test;

/**
 * {@link \Domain\Billing\DwsBillingInvoice} のテスト.
 */
final class DwsBillingInvoiceTest extends Test
{
    use CarbonMixin;
    use ExamplesConsumer;
    use UnitSupport;
    use MatchesSnapshots;

    protected DwsBillingInvoice $dwsBillingInvoice;

    protected array $values = [];
    protected array $dwsPaymentValues = [];
    protected array $highCostDwsPaymentValues = [];
    protected array $itemValues = [];

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (DwsBillingInvoiceTest $self): void {
            $self->dwsPaymentValues = [
                'subtotalDetailCount' => 10,
                'subtotalScore' => 10000,
                'subtotalFee' => 1000000,
                'subtotalBenefit' => 1000000,
                'subtotalCopay' => 37200,
                'subtotalSubsidy' => 100000,
            ];
            $self->highCostDwsPaymentValues = [
                'subtotalDetailCount' => 10,
                'subtotalFee' => 1000000,
                'subtotalBenefit' => 1000000,
            ];
            $self->itemValues = [
                'paymentCategory' => DwsBillingPaymentCategory::category1(),
                'serviceDivisionCode' => '12',
                'subtotalCount' => 10,
                'subtotalScore' => 1000,
                'subtotalFee' => 1000,
                'subtotalBenefit' => 1000,
                'subtotalCopay' => 1000,
                'subtotalSubsidy' => 1000,
                'sortOrder' => 1,
            ];
            $self->values = [
                'id' => 1,
                'dwsBillingBundleId' => 1,
                'claimAmount' => 100000,
                'dwsPayment' => DwsBillingPayment::create($self->dwsPaymentValues),
                'highCostDwsPayment' => DwsBillingHighCostPayment::create($self->highCostDwsPaymentValues),
                'totalCount' => 10,
                'totalScore' => 1000,
                'totalFee' => 1000,
                'totalBenefit' => 1000,
                'totalCopay' => 1000,
                'totalSubsidy' => 1000,
                'items' => [
                    DwsBillingInvoiceItem::create($self->itemValues),
                ],
            ];
            $self->dwsBillingInvoice = DwsBillingInvoice::create($self->values);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_dwsPayment(): void
    {
        $this->assertModelStrictEquals(
            DwsBillingInvoice::payment($this->dwsPaymentValues),
            DwsBillingPayment::create($this->dwsPaymentValues)
        );
    }

    /**
     * @test
     * @return void
     */
    public function describe_highCostDwsPayment(): void
    {
        $this->assertModelStrictEquals(
            DwsBillingInvoice::highCostPayment($this->highCostDwsPaymentValues),
            DwsBillingHighCostPayment::create($this->highCostDwsPaymentValues)
        );
    }

    /**
     * @test
     * @return void
     */
    public function describe_item(): void
    {
        $this->assertModelStrictEquals(
            DwsBillingInvoice::item($this->itemValues),
            DwsBillingInvoiceItem::create($this->itemValues)
        );
    }

    /**
     * @test
     * @return void
     */
    public function describe_attrs(): void
    {
        $examples = [
            'id' => ['id'],
            'dwsBillingBundleId' => ['dwsBillingBundleId'],
            'claimAmount' => ['claimAmount'],
            'dwsPayment' => ['dwsPayment'],
            'highCostDwsPayment' => ['highCostDwsPayment'],
            'totalCount' => ['totalCount'],
            'totalScore' => ['totalScore'],
            'totalFee' => ['totalFee'],
            'totalBenefit' => ['totalBenefit'],
            'totalCopay' => ['totalCopay'],
            'totalSubsidy' => ['totalSubsidy'],
            'items' => ['items'],
        ];

        $this->should(
            'have specified attribute',
            function (string $key): void {
                $this->assertSame($this->dwsBillingInvoice->get($key), Arr::get($this->values, $key));
            },
            compact('examples')
        );
    }

    /**
     * @test
     * @return void
     */
    public function describe_json(): void
    {
        $this->should('changes to JSON encode results', function (): void {
            $this->assertMatchesJsonSnapshot($this->dwsBillingInvoice);
        });
    }
}
