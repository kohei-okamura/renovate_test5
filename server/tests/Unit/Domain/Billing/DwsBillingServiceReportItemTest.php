<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\Billing;

use Domain\Billing\DwsBillingServiceReportDuration;
use Domain\Billing\DwsBillingServiceReportItem;
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
 * {@link \Domain\Billing\DwsBillingServiceReportItem} のテスト.
 */
final class DwsBillingServiceReportItemTest extends Test
{
    use CarbonMixin;
    use UnitSupport;
    use MatchesSnapshots;

    private DwsBillingServiceReportItem $item;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->item = DwsBillingServiceReportItem::create([
                'serialNumber' => 1,
                'providedOn' => Carbon::create(2020, 11),
                'serviceType' => DwsGrantedServiceCode::housework(),
                'providerType' => DwsBillingServiceReportProviderType::beginner(),
                'situation' => DwsBillingServiceReportSituation::hospitalized(),
                'plan' => DwsBillingServiceReportDuration::create([
                    'period' => CarbonRange::create([
                        'start' => Carbon::parse('2020-10-01'),
                        'end' => Carbon::parse('2020-11-01'),
                    ]),
                    'serviceDurationHours' => Decimal::fromInt(10_5000),
                    'movingDurationHours' => Decimal::fromInt(1_5000),
                ]),
                'result' => DwsBillingServiceReportDuration::create([
                    'period' => CarbonRange::create([
                        'start' => Carbon::parse('2020-10-01'),
                        'end' => Carbon::parse('2020-11-01'),
                    ]),
                    'serviceDurationHours' => Decimal::fromInt(10_5000),
                    'movingDurationHours' => Decimal::fromInt(1_5000),
                ]),
                'serviceCount' => 2,
                'headcount' => 1,
                'isCoaching' => true,
                'isFirstTime' => true,
                'isEmergency' => true,
                'isWelfareSpecialistCooperation' => true,
                'isBehavioralDisorderSupportCooperation' => true,
                'isMovingCareSupport' => true,
                'isDriving' => true,
                'isPreviousMonth' => true,
                'note' => '',
            ]);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_create(): void
    {
        $this->should('return an instance', function (): void {
            $this->assertMatchesModelSnapshot($this->item);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_json(): void
    {
        $this->should('changes to JSON encode results', function (): void {
            $this->assertMatchesJsonSnapshot($this->item);
        });
    }
}
