<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\Billing;

use Domain\Billing\DwsHomeHelpServiceDuration;
use Domain\Common\Carbon;
use Domain\ServiceCodeDictionary\DwsHomeHelpServiceProviderType;
use Domain\ServiceCodeDictionary\DwsServiceCodeCategory;
use Domain\ServiceCodeDictionary\Timeframe;
use Illuminate\Support\Arr;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Test;

/**
 * {@link \Domain\Billing\DwsHomeHelpServiceDuration} Test.
 */
class DwsHomeHelpServiceDurationTest extends Test
{
    use MatchesSnapshots;
    use UnitSupport;

    protected DwsHomeHelpServiceDuration $dwsHomeHelpServiceDuration;

    protected array $values = [];

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (DwsHomeHelpServiceDurationTest $self): void {
            $self->values = [
                'category' => DwsServiceCodeCategory::physicalCare(),
                'providerType' => DwsHomeHelpServiceProviderType::none(),
                'isSecondary' => false,
                'isSpanning' => false,
                'spanningDuration' => 0,
                'providedOn' => Carbon::parse('2020-01-01'),
                'timeframe' => Timeframe::daytime(),
                'duration' => 2,
                'headcount' => 1,
            ];
            $self->dwsHomeHelpServiceDuration = DwsHomeHelpServiceDuration::create($self->values);
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
            'providerType' => ['providerType'],
            'isSecondary' => ['isSecondary'],
            'isSpanning' => ['isSpanning'],
            'spanningDuration' => ['spanningDuration'],
            'providedOn' => ['providedOn'],
            'timeframe' => ['timeframe'],
            'duration' => ['duration'],
            'headcount' => ['headcount'],
        ];
        $this->should(
            'have specified attribute',
            function (string $key): void {
                $this->assertSame($this->dwsHomeHelpServiceDuration->get($key), Arr::get($this->values, $key));
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
            $this->assertMatchesJsonSnapshot($this->dwsHomeHelpServiceDuration);
        });
    }
}
