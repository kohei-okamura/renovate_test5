<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\Billing;

use Domain\Billing\DwsBillingServiceReportDuration;
use Domain\Common\Carbon;
use Domain\Common\CarbonRange;
use Domain\Common\Decimal;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Test;

/**
 * {@link \Domain\Billing\DwsBillingServiceReportDuration} のテスト.
 */
final class DwsBillingServiceReportDurationTest extends Test
{
    use CarbonMixin;
    use MatchesSnapshots;
    use UnitSupport;

    private DwsBillingServiceReportDuration $duration;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->duration = DwsBillingServiceReportDuration::create([
                'period' => CarbonRange::create([
                    'start' => Carbon::parse('2020-10-01'),
                    'end' => Carbon::parse('2020-11-01'),
                ]),
                'movingDurationHours' => Decimal::fromInt(1_5000),
                'serviceDurationHours' => Decimal::fromInt(10_5000),
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
            $this->assertMatchesModelSnapshot($this->duration);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_json(): void
    {
        $this->should('be able to encode to json', function (): void {
            $this->assertMatchesJsonSnapshot($this->duration);
        });
    }
}
