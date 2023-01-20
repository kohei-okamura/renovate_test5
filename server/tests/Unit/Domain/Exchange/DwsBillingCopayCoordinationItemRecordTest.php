<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\Exchange;

use Domain\Common\Carbon;
use Domain\Exchange\DwsBillingCopayCoordinationItemRecord;
use Lib\Arrays;
use Lib\Csv;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Test;

/**
 * {@link \Domain\Exchange\DwsBillingCopayCoordinationItemRecord} のテスト.
 */
final class DwsBillingCopayCoordinationItemRecordTest extends Test
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
                Csv::read(__DIR__ . '/DwsBillingCopayCoordinationItemRecordTestCsv.csv')->toArray(),
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
     * @return array&\Domain\Exchange\DwsBillingCopayCoordinationItemRecord[]
     */
    protected function examples(): array
    {
        return [
            3 => new DwsBillingCopayCoordinationItemRecord(
                providedIn: Carbon::create(2020, 3),
                cityCode: '112318',
                copayCoordinationOfficeCode: '1116507326',
                dwsNumber: '3100006729',
                itemNumber: 1,
                officeCode: '1116507326',
                fee: 59252,
                copay: 5925,
                coordinatedCopay: 5925,
            ),
        ];
    }
}
