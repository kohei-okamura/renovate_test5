<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Examples;

use Domain\Billing\DwsHomeHelpServiceDuration;
use Domain\Common\Carbon;
use Domain\ServiceCodeDictionary\DwsHomeHelpServiceProviderType;
use Domain\ServiceCodeDictionary\DwsServiceCodeCategory;
use Domain\ServiceCodeDictionary\Timeframe;
use Faker\Generator;

/**
 * DwsHomeHelpServiceDuration Example.
 *
 * @property-read \Domain\Billing\DwsHomeHelpServiceDuration[] $dwsHomeHelpServiceDurations
 * @mixin \Tests\Unit\Examples\UserExample
 */
trait DwsHomeHelpServiceDurationExample
{
    /**
     * 障害福祉サービス請求：サービス単位（居宅介護）時間帯別提供情報 の一覧を生成する.
     *
     * @return \Domain\Billing\DwsHomeHelpServiceDuration[]
     */
    protected function dwsHomeHelpServiceDurations(): array
    {
        $faker = app(Generator::class);
        return [
            $this->generateDwsHomeHelpServiceDuration([
                'category' => DwsServiceCodeCategory::physicalCare(),
                'timeframe' => Timeframe::daytime(),
            ], $faker),
        ];
    }

    private function generateDwsHomeHelpServiceDuration(array $overwrites, Generator $faker): DwsHomeHelpServiceDuration
    {
        $attrs = [
            'providerType' => $faker->randomElement(DwsHomeHelpServiceProviderType::all()),
            'isSecondary' => $faker->boolean,
            'isSpanning' => false,
            'spanningDuration' => 0,
            'providedOn' => Carbon::parse($faker->dateTimeBetween('-1 year')),
            'timeframe' => $faker->randomElement(Timeframe::all()),
            'duration' => 60,
            'headcount' => $faker->numberBetween(1, 2),
        ];

        return DwsHomeHelpServiceDuration::create($overwrites + $attrs);
    }
}
