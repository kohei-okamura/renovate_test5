<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\Billing;

use Domain\Billing\DwsBillingServiceReportDuration;
use Domain\Billing\DwsBillingServiceReportItem;
use Domain\Billing\DwsBillingServiceReportPdfDuration;
use Domain\Billing\DwsBillingServiceReportPdfItem;
use Domain\Billing\DwsBillingServiceReportProviderType;
use Domain\Billing\DwsBillingServiceReportSituation;
use Domain\Billing\DwsGrantedServiceCode;
use Domain\Common\Carbon;
use Domain\Common\CarbonRange;
use Domain\Common\Decimal;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Test;

/**
 * {@link \Domain\Billing\DwsBillingServiceReportPdfItem} のテスト.
 */
final class DwsBillingServiceReportPdfItemTest extends Test
{
    use CarbonMixin;
    use MatchesSnapshots;
    use UnitSupport;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
    }

    /**
     * @test
     * @return void
     */
    public function describe_construct(): void
    {
        $this->should('create DwsBillingServiceReportPdfItem', function (): void {
            $actual = new DwsBillingServiceReportPdfItem(
                providedOn: '1',
                weekday: '月',
                serviceCount: '1',
                serviceType: '身体',
                situation: '',
                plan: new DwsBillingServiceReportPdfDuration(
                    start: '',
                    end: '',
                    serviceDurationHours: '',
                    movingDurationHours: '',
                ),
                result: new DwsBillingServiceReportPdfDuration(
                    start: '10:00',
                    end: '12:00',
                    serviceDurationHours: '30',
                    movingDurationHours: '45',
                ),
                headcount: '1',
                isFirstTime: '1',
                isEmergency: '1',
                isWelfareSpecialistCooperation: '1',
                isMovingCareSupport: '1',
                note: 'びこう',
            );
            $this->assertMatchesModelSnapshot($actual);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_from(): void
    {
        $this->should('return DwsBillingServiceReportPdfItem', function (): void {
            $actual = DwsBillingServiceReportPdfItem::from([
                DwsBillingServiceReportItem::create([
                    'serialNumber' => 1,
                    'providedOn' => Carbon::today(),
                    'serviceType' => DwsGrantedServiceCode::visitingCareForPwsd1(),
                    'providerType' => DwsBillingServiceReportProviderType::beginner(),
                    'situation' => DwsBillingServiceReportSituation::hospitalized(),
                    'plan' => DwsBillingServiceReportDuration::create([
                        'period' => CarbonRange::create([
                            'start' => Carbon::today(),
                            'end' => Carbon::today()->addMonths(6),
                        ]),
                        'serviceDurationHours' => Decimal::fromInt(10_000),
                        'movingDurationHours' => Decimal::fromInt(5_0000),
                    ]),
                    'result' => DwsBillingServiceReportDuration::create([
                        'period' => CarbonRange::create([
                            'start' => Carbon::today(),
                            'end' => Carbon::today()->addMonths(6),
                        ]),
                        'serviceDurationHours' => Decimal::fromInt(8_000),
                        'movingDurationHours' => Decimal::fromInt(2_0000),
                    ]),
                    'serviceCount' => 1,
                    'headcount' => 2,
                    'isCoaching' => true,
                    'isFirstTime' => true,
                    'isEmergency' => true,
                    'isWelfareSpecialistCooperation' => true,
                    'isBehavioralDisorderSupportCooperation' => true,
                    'isMovingCareSupport' => true,
                    'isDriving' => true,
                    'isPreviousMonth' => true,
                    'note' => 'sample',
                ]),
            ]);
            $this->assertMatchesModelSnapshot($actual);
        });
    }
}
