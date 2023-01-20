<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\Exchange;

use Domain\Billing\DwsServiceDivisionCode;
use Domain\Common\Carbon;
use Domain\Exchange\DwsBillingStatementDaysRecord;
use Lib\Arrays;
use Lib\Csv;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Test;

/**
 * {@link \Domain\Exchange\DwsBillingStatementDaysRecord} のテスト.
 */
final class DwsBillingStatementDaysRecordTest extends Test
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
                Csv::read(__DIR__ . '/DwsBillingStatementDaysRecordTest.csv')->toArray(),
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
            29 => new DwsBillingStatementDaysRecord(
                providedIn: Carbon::create(2019, 10),
                cityCode: '131024',
                officeCode: '1311401366',
                dwsNumber: '0000010892',
                serviceDivisionCode: DwsServiceDivisionCode::visitingCareForPwsd(),
                startedOn: Carbon::create(2015, 12, 7),
                terminatedOn: null,
                serviceDays: 7,
            ),
            124 => new DwsBillingStatementDaysRecord(
                providedIn: Carbon::create(2020, 5),
                cityCode: '131041',
                officeCode: '1311401366',
                dwsNumber: '0000059220',
                serviceDivisionCode: DwsServiceDivisionCode::visitingCareForPwsd(),
                startedOn: Carbon::create(2018, 8, 20),
                terminatedOn: null,
                serviceDays: 30,
            ),
            686 => new DwsBillingStatementDaysRecord(
                providedIn: Carbon::create(1982, 8),
                cityCode: '131148',
                officeCode: '1311401366',
                dwsNumber: '3000024723',
                serviceDivisionCode: DwsServiceDivisionCode::homeHelpService(),
                startedOn: Carbon::create(2018, 9, 1),
                terminatedOn: Carbon::create(2019, 11, 30),
                serviceDays: 12,
            ),
        ];
    }
}
