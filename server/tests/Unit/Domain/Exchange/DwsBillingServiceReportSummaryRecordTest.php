<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\Exchange;

use Domain\Billing\DwsBillingServiceReportFormat;
use Domain\Common\Carbon;
use Domain\Common\Decimal;
use Domain\Exchange\DwsBillingServiceReportSummaryRecord;
use Lib\Arrays;
use Lib\Csv;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Test;

/**
 * {@link \Domain\Exchange\DwsBillingServiceReportSummaryRecord} のテスト.
 */
final class DwsBillingServiceReportSummaryRecordTest extends Test
{
    use ExamplesConsumer;
    use MatchesSnapshots;
    use UnitSupport;

    /**
     * @test
     * @return void
     */
    public function describe_toArray(): void
    {
        $this->should('should return valid csv values', function (): void {
            $this->assertEquals(
                Csv::read(__DIR__ . '/DwsBillingServiceReportSummaryRecordTest.csv')->toArray(),
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
     * @return array&\Domain\Exchange\DwsBillingServiceReportSummaryRecord[]
     */
    private function examples(): array
    {
        return [
            438 => new DwsBillingServiceReportSummaryRecord(
                providedIn: Carbon::create(2019, 02),
                cityCode: '232378',
                officeCode: '2316100896',
                dwsNumber: '8421000426',
                format: DwsBillingServiceReportFormat::homeHelpService(),
                totalPhysicalCare100: Decimal::fromInt(9_0000),
                totalPhysicalCare70: Decimal::zero(),
                totalPhysicalCarePwsd: Decimal::fromInt(3_0000),
                totalPhysicalCare: Decimal::fromInt(12_0000),
                totalAccompanyWithPhysicalCare100: Decimal::zero(),
                totalAccompanyWithPhysicalCare70: Decimal::zero(),
                totalAccompanyWithPhysicalCarePwsd: Decimal::zero(),
                totalAccompanyWithPhysicalCare: Decimal::zero(),
                totalHousework100: Decimal::fromInt(3_0000),
                totalHousework90: Decimal::fromInt(1_0000),
                totalHousework: Decimal::fromInt(4_0000),
                totalAccompany100: Decimal::zero(),
                totalAccompany90: Decimal::zero(),
                totalAccompany: Decimal::zero(),
                totalAccessibleTaxi100: Decimal::zero(),
                totalAccessibleTaxi90: Decimal::zero(),
                totalAccessibleTaxi: Decimal::zero(),
                movingDurationHours: Decimal::zero(),
                emergencyCount: 0,
                firstTimeCount: 0,
                welfareSpecialistCooperationCount: 0,
                behavioralDisorderSupportCooperationCount: 0,
                movingCareSupport: 0,
            ),
            53 => new DwsBillingServiceReportSummaryRecord(
                providedIn: Carbon::create(2019, 04),
                cityCode: '221309',
                officeCode: '2217271812',
                dwsNumber: '7000043526',
                format: DwsBillingServiceReportFormat::homeHelpService(),
                totalPhysicalCare100: Decimal::zero(),
                totalPhysicalCare70: Decimal::zero(),
                totalPhysicalCarePwsd: Decimal::fromInt(8_0000),
                totalPhysicalCare: Decimal::fromInt(8_0000),
                totalAccompanyWithPhysicalCare100: Decimal::zero(),
                totalAccompanyWithPhysicalCare70: Decimal::zero(),
                totalAccompanyWithPhysicalCarePwsd: Decimal::zero(),
                totalAccompanyWithPhysicalCare: Decimal::zero(),
                totalHousework100: Decimal::zero(),
                totalHousework90: Decimal::zero(),
                totalHousework: Decimal::zero(),
                totalAccompany100: Decimal::zero(),
                totalAccompany90: Decimal::zero(),
                totalAccompany: Decimal::zero(),
                totalAccessibleTaxi100: Decimal::zero(),
                totalAccessibleTaxi90: Decimal::zero(),
                totalAccessibleTaxi: Decimal::zero(),
                movingDurationHours: Decimal::zero(),
                emergencyCount: 0,
                firstTimeCount: 0,
                welfareSpecialistCooperationCount: 0,
                behavioralDisorderSupportCooperationCount: 0,
                movingCareSupport: 0,
            ),
            131 => new DwsBillingServiceReportSummaryRecord(
                providedIn: Carbon::create(2019, 9),
                cityCode: '221309',
                officeCode: '2217271812',
                dwsNumber: '7000008214',
                format: DwsBillingServiceReportFormat::visitingCareForPwsd(),
                totalPhysicalCare100: Decimal::zero(),
                totalPhysicalCare70: Decimal::zero(),
                totalPhysicalCarePwsd: Decimal::zero(),
                totalPhysicalCare: Decimal::fromInt(2_5000),
                totalAccompanyWithPhysicalCare100: Decimal::zero(),
                totalAccompanyWithPhysicalCare70: Decimal::zero(),
                totalAccompanyWithPhysicalCarePwsd: Decimal::zero(),
                totalAccompanyWithPhysicalCare: Decimal::zero(),
                totalHousework100: Decimal::zero(),
                totalHousework90: Decimal::zero(),
                totalHousework: Decimal::zero(),
                totalAccompany100: Decimal::zero(),
                totalAccompany90: Decimal::zero(),
                totalAccompany: Decimal::zero(),
                totalAccessibleTaxi100: Decimal::zero(),
                totalAccessibleTaxi90: Decimal::zero(),
                totalAccessibleTaxi: Decimal::zero(),
                movingDurationHours: Decimal::fromInt(2_0000),
                emergencyCount: 0,
                firstTimeCount: 0,
                welfareSpecialistCooperationCount: 0,
                behavioralDisorderSupportCooperationCount: 0,
                movingCareSupport: 0,
            ),
            140 => new DwsBillingServiceReportSummaryRecord(
                providedIn: Carbon::create(2019, 9),
                cityCode: '221309',
                officeCode: '2217271812',
                dwsNumber: '7000018809',
                format: DwsBillingServiceReportFormat::visitingCareForPwsd(),
                totalPhysicalCare100: Decimal::zero(),
                totalPhysicalCare70: Decimal::zero(),
                totalPhysicalCarePwsd: Decimal::zero(),
                totalPhysicalCare: Decimal::fromInt(120_0000),
                totalAccompanyWithPhysicalCare100: Decimal::zero(),
                totalAccompanyWithPhysicalCare70: Decimal::zero(),
                totalAccompanyWithPhysicalCarePwsd: Decimal::zero(),
                totalAccompanyWithPhysicalCare: Decimal::zero(),
                totalHousework100: Decimal::zero(),
                totalHousework90: Decimal::zero(),
                totalHousework: Decimal::zero(),
                totalAccompany100: Decimal::zero(),
                totalAccompany90: Decimal::zero(),
                totalAccompany: Decimal::zero(),
                totalAccessibleTaxi100: Decimal::zero(),
                totalAccessibleTaxi90: Decimal::zero(),
                totalAccessibleTaxi: Decimal::zero(),
                movingDurationHours: Decimal::fromInt(8_0000),
                emergencyCount: 0,
                firstTimeCount: 0,
                welfareSpecialistCooperationCount: 0,
                behavioralDisorderSupportCooperationCount: 0,
                movingCareSupport: 0,
            ),
            132 => new DwsBillingServiceReportSummaryRecord(
                providedIn: Carbon::create(2019, 12),
                cityCode: '131041',
                officeCode: '1310402167',
                dwsNumber: '0000058933',
                format: DwsBillingServiceReportFormat::homeHelpService(),
                totalPhysicalCare100: Decimal::zero(),
                totalPhysicalCare70: Decimal::zero(),
                totalPhysicalCarePwsd: Decimal::zero(),
                totalPhysicalCare: Decimal::zero(),
                totalAccompanyWithPhysicalCare100: Decimal::fromInt(6_0000),
                totalAccompanyWithPhysicalCare70: Decimal::zero(),
                totalAccompanyWithPhysicalCarePwsd: Decimal::zero(),
                totalAccompanyWithPhysicalCare: Decimal::fromInt(6_0000),
                totalHousework100: Decimal::zero(),
                totalHousework90: Decimal::zero(),
                totalHousework: Decimal::zero(),
                totalAccompany100: Decimal::zero(),
                totalAccompany90: Decimal::zero(),
                totalAccompany: Decimal::zero(),
                totalAccessibleTaxi100: Decimal::zero(),
                totalAccessibleTaxi90: Decimal::zero(),
                totalAccessibleTaxi: Decimal::zero(),
                movingDurationHours: Decimal::zero(),
                emergencyCount: 0,
                firstTimeCount: 0,
                welfareSpecialistCooperationCount: 0,
                behavioralDisorderSupportCooperationCount: 0,
                movingCareSupport: 0,
            ),
            356 => new DwsBillingServiceReportSummaryRecord(
                providedIn: Carbon::create(2019, 01),
                cityCode: '232378',
                officeCode: '2316100896',
                dwsNumber: '8421000426',
                format: DwsBillingServiceReportFormat::homeHelpService(),
                totalPhysicalCare100: Decimal::zero(),
                totalPhysicalCare70: Decimal::zero(),
                totalPhysicalCarePwsd: Decimal::fromInt(3_0000),
                totalPhysicalCare: Decimal::fromInt(3_0000),
                totalAccompanyWithPhysicalCare100: Decimal::zero(),
                totalAccompanyWithPhysicalCare70: Decimal::zero(),
                totalAccompanyWithPhysicalCarePwsd: Decimal::zero(),
                totalAccompanyWithPhysicalCare: Decimal::zero(),
                totalHousework100: Decimal::zero(),
                totalHousework90: Decimal::fromInt(1_0000),
                totalHousework: Decimal::fromInt(1_0000),
                totalAccompany100: Decimal::zero(),
                totalAccompany90: Decimal::zero(),
                totalAccompany: Decimal::zero(),
                totalAccessibleTaxi100: Decimal::zero(),
                totalAccessibleTaxi90: Decimal::zero(),
                totalAccessibleTaxi: Decimal::zero(),
                movingDurationHours: Decimal::zero(),
                emergencyCount: 0,
                firstTimeCount: 0,
                welfareSpecialistCooperationCount: 0,
                behavioralDisorderSupportCooperationCount: 0,
                movingCareSupport: 0,
            ),
        ];
    }
}
