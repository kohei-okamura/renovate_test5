<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\Exchange;

use Domain\Billing\DwsBillingInvoice;
use Domain\Common\Carbon;
use Domain\Exchange\DwsBillingInvoiceSummaryRecord;
use Lib\Arrays;
use Lib\Csv;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Test;

/**
 * {@link \Domain\Exchange\DwsBillingInvoiceSummaryRecord} のテスト.
 */
final class DwsBillingInvoiceSummaryRecordTest extends Test
{
    use UnitSupport;

    /**
     * @test
     * @return void
     */
    public function describe_toArray(): void
    {
        $this->should('should return valid csv values', function (): void {
            $this->assertEquals(
                Csv::read(__DIR__ . '/DwsBillingInvoiceSummaryRecordTest.csv')->toArray(),
                Arrays::generate(function (): iterable {
                    foreach ($this->examples() as $recordNumber => $record) {
                        yield $record->toArray($recordNumber);
                    }
                })
            );
        });
    }

    /**
     * Examples.
     *
     * @return array&\Domain\Exchange\DwsBillingInvoiceSummaryRecord[]
     */
    protected function examples(): array
    {
        return [
            293 => new DwsBillingInvoiceSummaryRecord(
                providedIn: Carbon::create(2019, 10),
                cityCode: '131083',
                officeCode: '1311401366',
                claimAmount: 1723047,
                dwsPayment: DwsBillingInvoice::payment([
                    'subtotalDetailCount' => 2,
                    'subtotalScore' => 157165,
                    'subtotalFee' => 1760247,
                    'subtotalBenefit' => 1723047,
                    'subtotalCopay' => 37200,
                    'subtotalSubsidy' => 0,
                ]),
                highCostDwsPayment: DwsBillingInvoice::highCostPayment([
                    'subtotalDetailCount' => 0,
                    'subtotalFee' => 0,
                    'subtotalBenefit' => 0,
                ]),
                totalCount: 2,
                totalScore: 157165,
                totalFee: 1760247,
                totalBenefit: 1723047,
                totalCopay: 37200,
                totalSubsidy: 0,
            ),
            941 => new DwsBillingInvoiceSummaryRecord(
                providedIn: Carbon::create(2020, 12),
                cityCode: '131202',
                officeCode: '1311401366',
                claimAmount: 2668857,
                dwsPayment: DwsBillingInvoice::payment([
                    'subtotalDetailCount' => 6,
                    'subtotalScore' => 238291,
                    'subtotalFee' => 2668857,
                    'subtotalBenefit' => 2668857,
                    'subtotalCopay' => 0,
                    'subtotalSubsidy' => 0,
                ]),
                highCostDwsPayment: DwsBillingInvoice::highCostPayment([
                    'subtotalDetailCount' => 0,
                    'subtotalFee' => 0,
                    'subtotalBenefit' => 0,
                ]),
                totalCount: 6,
                totalScore: 238291,
                totalFee: 1760247,
                totalBenefit: 2668857,
                totalCopay: 0,
                totalSubsidy: 0,
            ),
            1353 => new DwsBillingInvoiceSummaryRecord(
                providedIn: Carbon::create(2008, 5),
                cityCode: '132047',
                officeCode: '1311401366',
                claimAmount: 2750263,
                dwsPayment: DwsBillingInvoice::payment([
                    'subtotalDetailCount' => 2,
                    'subtotalScore' => 249711,
                    'subtotalFee' => 2796763,
                    'subtotalBenefit' => 2750263,
                    'subtotalCopay' => 46500,
                    'subtotalSubsidy' => 0,
                ]),
                highCostDwsPayment: DwsBillingInvoice::highCostPayment([
                    'subtotalDetailCount' => 0,
                    'subtotalFee' => 0,
                    'subtotalBenefit' => 0,
                ]),
                totalCount: 2,
                totalScore: 249711,
                totalFee: 2796763,
                totalBenefit: 2750263,
                totalCopay: 46500,
                totalSubsidy: 0,
            ),
        ];
    }
}
