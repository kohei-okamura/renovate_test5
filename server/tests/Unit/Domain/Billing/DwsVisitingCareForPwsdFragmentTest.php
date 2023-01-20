<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\Billing;

use Domain\Billing\DwsVisitingCareForPwsdFragment;
use Domain\Common\CarbonRange;
use Illuminate\Support\Arr;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Test;

/**
 * {@link \Domain\Billing\DwsVisitingCareForPwsdFragment} Test.
 */
class DwsVisitingCareForPwsdFragmentTest extends Test
{
    use MatchesSnapshots;
    use UnitSupport;

    protected DwsVisitingCareForPwsdFragment $dwsVisitingCareForPwsdFragment;

    protected array $values = [];

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (DwsVisitingCareForPwsdFragmentTest $self): void {
            $self->values = [
                'isHospitalized' => false,
                'isLongHospitalized' => true,
                'isCoaching' => true,
                'isMoving' => true,
                'isSecondary' => true,
                'movingDurationMinutes' => 0,
                'range' => CarbonRange::create(),
                'headcount' => 1,
            ];
            $self->dwsVisitingCareForPwsdFragment = DwsVisitingCareForPwsdFragment::create($self->values);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_attrs(): void
    {
        $examples = [
            'isHospitalized' => ['isHospitalized'],
            'isLongHospitalized' => ['isLongHospitalized'],
            'isCoaching' => ['isCoaching'],
            'isMoving' => ['isMoving'],
            'isSecondary' => ['isSecondary'],
            'movingDurationMinutes' => ['movingDurationMinutes'],
            'range' => ['range'],
            'headcount' => ['headcount'],
        ];
        $this->should(
            'have specified attribute',
            function (string $key): void {
                $this->assertSame($this->dwsVisitingCareForPwsdFragment->get($key), Arr::get($this->values, $key));
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
            $this->assertMatchesJsonSnapshot($this->dwsVisitingCareForPwsdFragment);
        });
    }
}
