<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\Billing;

use Domain\Billing\DwsHomeHelpServiceFragment;
use Domain\Common\Carbon;
use Domain\Common\CarbonRange;
use Domain\ServiceCodeDictionary\DwsHomeHelpServiceProviderType;
use Illuminate\Support\Arr;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Test;

/**
 * {@link \Domain\Billing\DwsHomeHelpServiceFragment} Test.
 */
class DwsHomeHelpServiceFragmentTest extends Test
{
    use MatchesSnapshots;
    use UnitSupport;

    protected DwsHomeHelpServiceFragment $dwsHomeHelpServiceFragment;

    protected array $values = [];

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (DwsHomeHelpServiceFragmentTest $self): void {
            $self->values = [
                'providerType' => DwsHomeHelpServiceProviderType::none(),
                'isSecondary' => true,
                'range' => CarbonRange::create([
                    'start' => Carbon::parse('2020-11-09 12:00'),
                    'end' => Carbon::parse('2020-11-09 13:00'),
                ]),
                'headcount' => 1,
            ];
            $self->dwsHomeHelpServiceFragment = DwsHomeHelpServiceFragment::create($self->values);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_attrs(): void
    {
        $examples = [
            'providerType' => ['providerType'],
            'isSecondary' => ['isSecondary'],
            'range' => ['range'],
            'headcount' => ['headcount'],
        ];
        $this->should(
            'have specified attribute',
            function (string $key): void {
                $this->assertSame($this->dwsHomeHelpServiceFragment->get($key), Arr::get($this->values, $key));
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
            $this->assertMatchesJsonSnapshot($this->dwsHomeHelpServiceFragment);
        });
    }
}
