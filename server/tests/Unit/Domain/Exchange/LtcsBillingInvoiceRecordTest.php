<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\Exchange;

use Domain\Common\Carbon;
use Domain\Common\DefrayerCategory;
use Domain\Exchange\LtcsBillingInvoiceRecord;
use Lib\Arrays;
use Lib\Csv;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Test;

/**
 * {@link \Domain\Exchange\LtcsBillingInvoiceRecord} のテスト.
 */
final class LtcsBillingInvoiceRecordTest extends Test
{
    use UnitSupport;

    /**
     * @test
     * @return void
     */
    public function describe_toArray(): void
    {
        $this->should('return valid csv values', function (): void {
            $this->assertEquals(
                Csv::read(__DIR__ . '/LtcsBillingInvoiceRecordTest.csv')->toArray(),
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
     * @return array
     */
    private function examples(): array
    {
        return [
            2 => new LtcsBillingInvoiceRecord(
                providedIn: Carbon::create(2019, 1),
                officeCode: '1370406140',
                invoiceType: LtcsBillingInvoiceRecord::INVOICE_TYPE_SUBSIDY,
                defrayerCategory: DefrayerCategory::livelihoodProtection(),
                billingCategory: LtcsBillingInvoiceRecord::BILLING_CATEGORY_SERVICE_PROVISION,
                statementCount: 3,
                totalScore: 16733,
                totalFee: 190755,
                insuranceAmount: 0,
                subsidyAmount: 65369,
                copayAmount: 0,
            ),
            3 => new LtcsBillingInvoiceRecord(
                providedIn: Carbon::create(2008, 5),
                officeCode: '1370406140',
                invoiceType: LtcsBillingInvoiceRecord::INVOICE_TYPE_INSURANCE,
                defrayerCategory: null,
                billingCategory: LtcsBillingInvoiceRecord::BILLING_CATEGORY_SERVICE_PROVISION,
                statementCount: 25,
                totalScore: 134944,
                totalFee: 1538350,
                insuranceAmount: 1342115,
                subsidyAmount: 15089,
                copayAmount: 181146,
            ),
            4 => new LtcsBillingInvoiceRecord(
                providedIn: Carbon::create(2020, 12),
                officeCode: '1370406140',
                invoiceType: LtcsBillingInvoiceRecord::INVOICE_TYPE_SUBSIDY,
                defrayerCategory: DefrayerCategory::supportForJapaneseReturneesFromChina(),
                billingCategory: LtcsBillingInvoiceRecord::BILLING_CATEGORY_NONE,
                statementCount: 1,
                totalScore: 1014,
                totalFee: 11559,
                insuranceAmount: 0,
                subsidyAmount: 1156,
                copayAmount: 0,
            ),
        ];
    }
}
