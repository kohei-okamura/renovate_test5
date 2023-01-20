<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\Exchange;

use Domain\Billing\DwsBillingPaymentCategory;
use Domain\Billing\DwsServiceDivisionCode;
use Domain\Common\Carbon;
use Domain\Exchange\DwsBillingInvoiceItemRecord;
use Lib\Arrays;
use Lib\Csv;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Test;

/**
 * {@link \Domain\Exchange\DwsBillingInvoiceItemRecord} のテスト.
 */
final class DwsBillingInvoiceItemRecordTest extends Test
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
                Csv::read(__DIR__ . '/DwsBillingInvoiceItemRecordTest.csv')->toArray(),
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
     * @return array&\Domain\Exchange\DwsBillingInvoiceItemRecord[]
     */
    protected function examples(): array
    {
        return [
            66 => new DwsBillingInvoiceItemRecord(
                providedIn: Carbon::create(2019, 10),
                cityCode: '131041',
                officeCode: '1311401366',
                paymentCategory: DwsBillingPaymentCategory::category1(),
                serviceDivisionCode: DwsServiceDivisionCode::visitingCareForPwsd(),
                subtotalCount: 6,
                subtotalScore: 314850,
                subtotalFee: 3526318,
                subtotalBenefit: 3526318,
                subtotalCopay: 0,
                subtotalSubsidy: 0,
            ),
            444 => new DwsBillingInvoiceItemRecord(
                providedIn: Carbon::create(2008, 5),
                cityCode: '131121',
                officeCode: '1311401366',
                paymentCategory: DwsBillingPaymentCategory::category1(),
                serviceDivisionCode: DwsServiceDivisionCode::visitingCareForPwsd(),
                subtotalCount: 9,
                subtotalScore: 477858,
                subtotalFee: 5352006,
                subtotalBenefit: 5352006,
                subtotalCopay: 46500,
                subtotalSubsidy: 0,
            ),
            1063 => new DwsBillingInvoiceItemRecord(
                providedIn: Carbon::create(2020, 10),
                cityCode: '131211',
                officeCode: '1311401366',
                paymentCategory: DwsBillingPaymentCategory::category1(),
                serviceDivisionCode: DwsServiceDivisionCode::visitingCareForPwsd(),
                subtotalCount: 4,
                subtotalScore: 228201,
                subtotalFee: 2555849,
                subtotalBenefit: 2518649,
                subtotalCopay: 37200,
                subtotalSubsidy: 0,
            ),
        ];
    }
}
