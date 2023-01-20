<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\Billing;

use Domain\Billing\DwsVisitingCareForPwsdDuration;
use Domain\Common\Carbon;
use Domain\ServiceCodeDictionary\DwsServiceCodeCategory;
use Domain\ServiceCodeDictionary\Timeframe;
use Illuminate\Support\Arr;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Test;

/**
 * {@link \Domain\Billing\DwsVisitingCareForPwsdDuration} Test.
 */
class DwsVisitingCareForPwsdDurationTest extends Test
{
    use CarbonMixin;
    use MatchesSnapshots;
    use UnitSupport;

    protected DwsVisitingCareForPwsdDuration $dwsVisitingCareForPwsdDuration;

    protected array $values = [];

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (DwsVisitingCareForPwsdDurationTest $self): void {
            $self->values = [
                'category' => DwsServiceCodeCategory::visitingCareForPwsd1(),
                'isHospitalized' => true,
                'isLongHospitalized' => false,
                'isCoaching' => true,
                'isMoving' => true,
                'isSecondary' => true,
                'providedOn' => Carbon::now(),
                'timeframe' => Timeframe::daytime(),
                'duration' => 240,
                'headcount' => 1,
            ];
            $self->dwsVisitingCareForPwsdDuration = DwsVisitingCareForPwsdDuration::create($self->values);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_attrs(): void
    {
        $examples = [
            'category' => ['category'],
            'isHospitalized' => ['isHospitalized'],
            'isLongHospitalized' => ['isLongHospitalized'],
            'isCoaching' => ['isCoaching'],
            'isMoving' => ['isMoving'],
            'isSecondary' => ['isSecondary'],
            'providedOn' => ['providedOn'],
            'timeframe' => ['timeframe'],
            'duration' => ['duration'],
            'headcount' => ['headcount'],
        ];
        $this->should(
            'have specified attribute',
            function (string $key): void {
                $this->assertSame($this->dwsVisitingCareForPwsdDuration->get($key), Arr::get($this->values, $key));
            },
            compact('examples')
        );
    }

    /**
     * @test
     * @return void
     */
    public function describe_json(): void
    {
        $this->should('changes to JSON encode results', function (): void {
            $this->assertMatchesJsonSnapshot($this->dwsVisitingCareForPwsdDuration);
        });
    }
}
