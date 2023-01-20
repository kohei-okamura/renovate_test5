<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\Exchange;

use Domain\Billing\CopayCoordinationResult;
use Domain\Billing\DwsBillingCopayCoordinationExchangeAim;
use Domain\Common\Carbon;
use Domain\Exchange\DwsBillingCopayCoordinationSummaryRecord;
use Lib\Arrays;
use Lib\Csv;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Test;

/**
 * {@link \Domain\Exchange\DwsBillingCopayCoordinationSummaryRecord} のテスト.
 */
final class DwsBillingCopayCoordinationSummaryRecordTest extends Test
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
                Csv::read(__DIR__ . '/DwsBillingCopayCoordinationSummaryRecordTestCsv.csv')->toArray(),
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
     * @return array&\Domain\Exchange\DwsBillingCopayCoordinationSummaryRecord[]
     */
    protected function examples(): array
    {
        return [
            2 => new DwsBillingCopayCoordinationSummaryRecord(
                providedIn: Carbon::create(2020, 3),
                copayCoordinationExchangeAim: DwsBillingCopayCoordinationExchangeAim::declaration(),
                cityCode: '112318',
                copayCoordinationOfficeCode: '1116507326',
                dwsNumber: '3100006729',
                userPhoneticDisplayName: 'ヤマダタロウ',
                childPhoneticDisplayName: '',
                copayLimit: 9300,
                result: CopayCoordinationResult::coordinated(),
                totalFee: 345373,
                totalCopay: 13651,
                coordinatedCopay: 9300,
            ),
        ];
    }
}
