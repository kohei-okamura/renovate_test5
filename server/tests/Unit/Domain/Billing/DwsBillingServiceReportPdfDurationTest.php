<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\Billing;

use Domain\Billing\DwsBillingServiceReportPdfDuration;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * {@link \Domain\Billing\DwsBillingServiceReportPdfDuration} のテスト.
 */
final class DwsBillingServiceReportPdfDurationTest extends Test
{
    use MatchesSnapshots;
    use MockeryMixin;
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
        $this->should('create DwsBillingServiceReportPdfDuration', function (): void {
            $actual = new DwsBillingServiceReportPdfDuration(
                start: '1:00',
                end: '2:00',
                serviceDurationHours: '30',
                movingDurationHours: '10',
            );
            $this->assertMatchesModelSnapshot($actual);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_empty(): void
    {
        $this->should('create Empty DwsBillingServiceReportPdfDuration', function (): void {
            $expect = new DwsBillingServiceReportPdfDuration(
                start: '',
                end: '',
                serviceDurationHours: '',
                movingDurationHours: '',
            );
            $this->assertModelStrictEquals($expect, DwsBillingServiceReportPdfDuration::empty());
        });
    }
}
