<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\Billing;

use Domain\Billing\DwsBillingHighCostPayment;
use Domain\Billing\DwsBillingInvoiceItem;
use Domain\Billing\DwsBillingInvoicePdf;
use Domain\Billing\DwsBillingOffice;
use Domain\Billing\DwsBillingPayment;
use Domain\Billing\DwsBillingPaymentCategory;
use Domain\Common\Carbon;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Test;

/**
 * {@link \Domain\Billing\DwsBillingInvoicePdf} のテスト.
 */
final class DwsBillingInvoicePdfTest extends Test
{
    use CarbonMixin;
    use ExamplesConsumer;
    use MatchesSnapshots;
    use UnitSupport;

    /**
     * @test
     * @return void
     */
    public function describe_create(): void
    {
        $this->should('return an instance', function (): void {
            $actual = $this->createInstance();
            $this->assertMatchesModelSnapshot($actual);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_from(): void
    {
        $this->should('return an instance', function (): void {
            // FYI: テスト実行ごとにランダムに生成される `examples` を用いているためスナップショットテストができない
            $billing = $this->examples->dwsBillings[0];
            $bundle = $this->examples->dwsBillingBundles[0];
            $invoice = $this->examples->dwsBillingInvoices[0];
            $expected = new DwsBillingInvoicePdf(
                destinationName: $bundle->cityName . '長',
                office: $billing->office,
                providedIn: [
                    'japaneseCalender' => mb_substr($bundle->providedIn->formatLocalized('%EC%Ey'), 0, 2),
                    'year' => mb_substr($bundle->providedIn->formatLocalized('%EC%Ey'), 2),
                    'month' => $bundle->providedIn->format('m'),
                    'day' => $bundle->providedIn->format('d'),
                ],
                claimAmount: preg_split('//u', sprintf('% 9d', $invoice->claimAmount)),
                items: $invoice->items,
                dwsPayment: $invoice->dwsPayment,
                highCostDwsPayment: $invoice->highCostDwsPayment,
                totalCount: $invoice->totalCount,
                totalScore: $invoice->totalScore,
                totalFee: $invoice->totalFee,
                totalBenefit: $invoice->totalBenefit,
                totalCopay: $invoice->totalCopay,
                totalSubsidy: $invoice->totalSubsidy,
                issuedOn: Carbon::now()->toJapaneseDate(),
            );

            $actual = DwsBillingInvoicePdf::from($billing, $bundle, $invoice);

            $this->assertModelStrictEquals($expected, $actual);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_json(): void
    {
        $this->should('changes to JSON encode results', function (): void {
            $actual = $this->createInstance()->toJson();
            $this->assertMatchesJsonSnapshot($actual);
        });
    }

    /**
     * テスト対象のインスタンスを生成する.
     *
     * @param array $attrs
     * @return \Domain\Billing\DwsBillingInvoicePdf
     */
    private function createInstance(array $attrs = []): DwsBillingInvoicePdf
    {
        $instance = new DwsBillingInvoicePdf(
            destinationName: '',
            office: DwsBillingOffice::create([]),
            providedIn: [
                'japaneseCalender' => '',
                'year' => '',
                'month' => '',
                'day' => '',
            ],
            claimAmount: ['', ' ', ' ', ' ', '1', '0', '0', '0', '0', '0', ''],
            items: [
                DwsBillingInvoiceItem::create([
                    'paymentCategory' => DwsBillingPaymentCategory::category1(),
                    'serviceDivisionCode' => '12',
                    'subtotalCount' => 10,
                    'subtotalScore' => 1000,
                    'subtotalFee' => 1000,
                    'subtotalBenefit' => 1000,
                    'subtotalCopay' => 1000,
                    'subtotalSubsidy' => 1000,
                    'sortOrder' => 1,
                ]),
            ],
            dwsPayment: DwsBillingPayment::create([
                'subtotalDetailCount' => 10,
                'subtotalScore' => 10000,
                'subtotalFee' => 1000000,
                'subtotalBenefit' => 1000000,
                'subtotalCopay' => 37200,
                'subtotalSubsidy' => 100000,
            ]),
            highCostDwsPayment: DwsBillingHighCostPayment::create([
                'subtotalDetailCount' => 10,
                'subtotalFee' => 1000000,
                'subtotalBenefit' => 1000000,
            ]),
            totalCount: 10,
            totalScore: 1000,
            totalFee: 1000,
            totalBenefit: 1000,
            totalCopay: 1000,
            totalSubsidy: 1000,
            issuedOn: Carbon::now()->toJapaneseDate(),
        );
        return $instance->copy($attrs);
    }
}
