<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\Billing;

use Domain\Billing\DwsVisitingCareForPwsdChunkImpl;
use Domain\Common\Carbon;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Test;

/**
 * {@link \Domain\Billing\DwsVisitingCareForPwsdChunkGetDayBoundaryMixin} のテスト.
 */
final class DwsVisitingCareForPwsdChunkGetDayBoundaryMixinTest extends Test
{
    use UnitSupport;

    /**
     * @test
     * @return void
     */
    public function describe_getDayBoundary(): void
    {
        $examples = [
            [Carbon::create(2021, 5, 10, 22, 0, 0), Carbon::create(2021, 5, 11, 0, 0, 0)],
            [Carbon::create(2021, 5, 10, 22, 10, 0), Carbon::create(2021, 5, 11, 0, 10, 0)],
            [Carbon::create(2021, 5, 10, 22, 20, 0), Carbon::create(2021, 5, 11, 0, 20, 0)],
            [Carbon::create(2021, 5, 10, 22, 30, 0), Carbon::create(2021, 5, 11, 0, 0, 0)],
            [Carbon::create(2021, 5, 10, 22, 40, 0), Carbon::create(2021, 5, 11, 0, 10, 0)],
            [Carbon::create(2021, 5, 10, 22, 50, 0), Carbon::create(2021, 5, 11, 0, 20, 0)],
            [Carbon::create(2021, 5, 10, 23, 0, 0), Carbon::create(2021, 5, 11, 0, 0, 0)],
            [Carbon::create(2021, 5, 10, 23, 10, 0), Carbon::create(2021, 5, 11, 0, 10, 0)],
            [Carbon::create(2021, 5, 10, 23, 20, 0), Carbon::create(2021, 5, 11, 0, 20, 0)],
            [Carbon::create(2021, 5, 10, 23, 30, 0), Carbon::create(2021, 5, 11, 0, 30, 0)],
            [Carbon::create(2021, 5, 10, 23, 40, 0), Carbon::create(2021, 5, 11, 0, 40, 0)],
            [Carbon::create(2021, 5, 10, 23, 50, 0), Carbon::create(2021, 5, 11, 0, 50, 0)],
        ];
        $this->should(
            'return a valid boundary',
            function (Carbon $argument, Carbon $expected): void {
                $this->assertSame(
                    DwsVisitingCareForPwsdChunkImpl::getDayBoundary($argument)->timestamp,
                    $expected->timestamp
                );
            },
            compact('examples')
        );
    }
}
