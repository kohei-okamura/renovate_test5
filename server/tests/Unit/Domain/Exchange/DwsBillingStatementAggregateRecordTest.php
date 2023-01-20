<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\Exchange;

use Domain\Billing\DwsServiceDivisionCode;
use Domain\Common\Carbon;
use Domain\Common\Decimal;
use Domain\Exchange\DwsBillingStatementAggregateRecord;
use Lib\Arrays;
use Lib\Csv;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Test;

/**
 * {@link \Domain\Exchange\DwsBillingStatementAggregateRecord} のテスト.
 */
final class DwsBillingStatementAggregateRecordTest extends Test
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
                Csv::read(__DIR__ . '/DwsBillingStatementAggregateRecordTest.csv')->toArray(),
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
     * @return array&\Domain\Exchange\DwsBillingStatementAggregateRecord[]
     */
    private function examples(): array
    {
        return [
            17 => new DwsBillingStatementAggregateRecord(
                providedIn: Carbon::create(2022, 1),
                cityCode: '075434',
                officeCode: '1116507326',
                dwsNumber: '0754303113',
                serviceDivisionCode: DwsServiceDivisionCode::visitingCareForPwsd(),
                serviceDays: 25,
                subtotalScore: 58369,
                unitCost: Decimal::fromInt(10_9000),
                subtotalFee: 636222,
                unmanagedCopay: 63622,
                managedCopay: 0,
                cappedCopay: 0,
                adjustedCopay: null,
                coordinatedCopay: null,
                subtotalCopay: 0,
                subtotalBenefit: 636222,
                subtotalSubsidy: null,
            ),
            63 => new DwsBillingStatementAggregateRecord(
                providedIn: Carbon::create(2019, 10),
                cityCode: '131032',
                officeCode: '1311401366',
                dwsNumber: '8000035272',
                serviceDivisionCode: DwsServiceDivisionCode::visitingCareForPwsd(),
                serviceDays: 28,
                subtotalScore: 66783,
                unitCost: Decimal::fromInt(11_2000),
                subtotalFee: 747969,
                unmanagedCopay: 74796,
                managedCopay: 74796,
                cappedCopay: 37200,
                adjustedCopay: null,
                coordinatedCopay: null,
                subtotalCopay: 37200,
                subtotalBenefit: 710769,
                subtotalSubsidy: null,
            ),
            190 => new DwsBillingStatementAggregateRecord(
                providedIn: Carbon::create(2020, 2),
                cityCode: '131059',
                officeCode: '1311401366',
                dwsNumber: '1305038554',
                serviceDivisionCode: DwsServiceDivisionCode::visitingCareForPwsd(),
                serviceDays: 20,
                subtotalScore: 46585,
                unitCost: Decimal::fromInt(11_2000),
                subtotalFee: 521752,
                unmanagedCopay: 52175,
                managedCopay: 52175,
                cappedCopay: 9300,
                adjustedCopay: null,
                coordinatedCopay: 9300,
                subtotalCopay: 9300,
                subtotalBenefit: 512452,
                subtotalSubsidy: null,
            ),
            811 => new DwsBillingStatementAggregateRecord(
                providedIn: Carbon::create(2008, 5),
                cityCode: '131164',
                officeCode: '1311401366',
                dwsNumber: '1000048767',
                serviceDivisionCode: DwsServiceDivisionCode::visitingCareForPwsd(),
                serviceDays: 8,
                subtotalScore: 3444,
                unitCost: Decimal::fromInt(11_2000),
                subtotalFee: 38572,
                unmanagedCopay: 3857,
                managedCopay: 3857,
                cappedCopay: 3857,
                adjustedCopay: null,
                coordinatedCopay: 0,
                subtotalCopay: 0,
                subtotalBenefit: 38572,
                subtotalSubsidy: null,
            ),
        ];
    }
}
