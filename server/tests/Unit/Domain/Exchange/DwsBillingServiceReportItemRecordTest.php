<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\Exchange;

use Domain\Billing\DwsBillingServiceReportFormat;
use Domain\Billing\DwsBillingServiceReportProviderType;
use Domain\Billing\DwsBillingServiceReportSituation;
use Domain\Billing\DwsGrantedServiceCode;
use Domain\Common\Carbon;
use Domain\Common\Decimal;
use Domain\Common\TimeRange;
use Domain\Exchange\DwsBillingServiceReportItemRecord;
use Lib\Arrays;
use Lib\Csv;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Test;

/**
 * {@link \Domain\Exchange\DwsBillingServiceReportItemRecord} のテスト.
 */
final class DwsBillingServiceReportItemRecordTest extends Test
{
    use CarbonMixin;
    use UnitSupport;

    /**
     * @test
     * @return void
     */
    public function describe_toArray(): void
    {
        $this->should('should return valid csv values', function (): void {
            $this->assertEquals(
                Csv::read(__DIR__ . '/DwsBillingServiceReportItemRecordCsv.csv')->toArray(),
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
     * @return array&\Domain\Exchange\DwsBillingServiceReportItemRecord[]
     */
    private function examples(): array
    {
        return [
            107 => new DwsBillingServiceReportItemRecord(
                providedIn: Carbon::create(2019, 11),
                cityCode: '131041',
                officeCode: '1310402167',
                dwsNumber: '0000054528',
                format: DwsBillingServiceReportFormat::homeHelpService(),
                serialNumber: 1,
                providedOn: Carbon::create(2019, 11, 2),
                serviceCount: 0,
                dwsGrantedServiceCode: DwsGrantedServiceCode::physicalCare(),
                providerType: DwsBillingServiceReportProviderType::novice(),
                isDriving: false,
                period: TimeRange::create(['start' => '17:30', 'end' => '18:00']),
                serviceDurationHours: Decimal::fromInt(5000),
                movingDurationHours: null,
                headcount: 1,
                isPreviousMonth: false,
                note: '',
                situation: DwsBillingServiceReportSituation::none(),
                isEmergency: false,
                isFirstTime: false,
                isWelfareSpecialistCooperation: false,
                isBehavioralDisorderSupportCooperation: false,
                isCoaching: false,
                isMovingCareSupport: false,
            ),
            57 => new DwsBillingServiceReportItemRecord(
                providedIn: Carbon::create(2019, 11),
                cityCode: '132012',
                officeCode: '1312501065',
                dwsNumber: '2010013809',
                format: DwsBillingServiceReportFormat::visitingCareForPwsd(),
                serialNumber: 3,
                providedOn: Carbon::create(2019, 11, 4),
                serviceCount: 0,
                dwsGrantedServiceCode: DwsGrantedServiceCode::none(),
                providerType: DwsBillingServiceReportProviderType::none(),
                isDriving: false,
                period: TimeRange::create(['start' => '21:00', 'end' => '00:00']),
                serviceDurationHours: Decimal::fromInt(11_0000),
                movingDurationHours: null,
                headcount: 1,
                isPreviousMonth: false,
                note: '',
                situation: DwsBillingServiceReportSituation::none(),
                isEmergency: false,
                isFirstTime: false,
                isWelfareSpecialistCooperation: false,
                isBehavioralDisorderSupportCooperation: false,
                isCoaching: false,
                isMovingCareSupport: false,
            ),
            85 => new DwsBillingServiceReportItemRecord(
                providedIn: Carbon::create(2020, 1),
                cityCode: '142059',
                officeCode: '1412202200',
                dwsNumber: '0000013375',
                format: DwsBillingServiceReportFormat::visitingCareForPwsd(),
                serialNumber: 1,
                providedOn: Carbon::create(2020, 1, 3),
                serviceCount: 1,
                dwsGrantedServiceCode: DwsGrantedServiceCode::none(),
                providerType: DwsBillingServiceReportProviderType::none(),
                isDriving: false,
                period: TimeRange::create(['start' => '21:30', 'end' => '00:00']),
                serviceDurationHours: Decimal::fromInt(2_5000),
                movingDurationHours: null,
                headcount: 1,
                isPreviousMonth: false,
                note: '',
                situation: DwsBillingServiceReportSituation::none(),
                isEmergency: false,
                isFirstTime: false,
                isWelfareSpecialistCooperation: false,
                isBehavioralDisorderSupportCooperation: false,
                isCoaching: true,
                isMovingCareSupport: true,
            ),
        ];
    }
}
