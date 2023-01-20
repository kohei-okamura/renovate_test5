<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Examples;

use Domain\Billing\DwsHomeHelpServiceChunkImpl;
use Domain\Billing\DwsHomeHelpServiceFragment;
use Domain\Common\Carbon;
use Domain\Common\CarbonRange;
use Domain\ServiceCodeDictionary\DwsHomeHelpServiceBuildingType;
use Domain\ServiceCodeDictionary\DwsHomeHelpServiceProviderType;
use Domain\ServiceCodeDictionary\DwsServiceCodeCategory;
use Faker\Generator;
use ScalikePHP\Seq;

/**
 * DwsHomeHelpServiceChunkImpl Example.
 *
 * @property-read \Domain\Billing\DwsHomeHelpServiceChunkImpl[] $dwsHomeHelpServiceChunks
 * @mixin \Tests\Unit\Examples\UserExample
 */
trait DwsHomeHelpServiceChunkExample
{
    /**
     * 障害福祉サービス請求：サービス単位（居宅介護）の一覧を生成する.
     *
     * @return \Domain\Billing\DwsHomeHelpServiceChunkImpl[]
     */
    protected function dwsHomeHelpServiceChunks(): array
    {
        $faker = app(Generator::class);
        return [
            $this->generateDwsHomeHelpServiceChunk([
                'userId' => $this->users[0]->id,
                'category' => DwsServiceCodeCategory::housework(),
            ], $faker),
            $this->generateDwsHomeHelpServiceChunk([
                'userId' => $this->users[0]->id,
                'category' => DwsServiceCodeCategory::physicalCare(),
                'buildingType' => DwsHomeHelpServiceBuildingType::over20(),
                'range' => CarbonRange::create([
                    'start' => Carbon::create('2020-12-12 12:00'),
                    'end' => Carbon::create('2020-12-12 15:00'),
                ]),
            ], $faker),
            $this->generateDwsHomeHelpServiceChunk([
                'userId' => $this->users[1]->id,
                'buildingType' => DwsHomeHelpServiceBuildingType::none(),
                'isEmergency' => false,
                'range' => CarbonRange::create([
                    'start' => Carbon::create('2020-12-12 22:00'),
                    'end' => Carbon::create('2020-12-12 23:00'),
                ]),
            ], $faker),
            $this->generateDwsHomeHelpServiceChunk([
                'userId' => $this->users[0]->id,
                'isEmergency' => true,
                'isPlannedByNovice' => false,
            ], $faker),
            $this->generateDwsHomeHelpServiceChunk([
                'userId' => $this->users[0]->id,
                'isPlannedByNovice' => true,
                'range' => CarbonRange::create([
                    'start' => Carbon::now(),
                    'end' => Carbon::now()->addHour(),
                ]),
            ], $faker),
            $this->generateDwsHomeHelpServiceChunk([
                'userId' => $this->users[0]->id,
                'range' => CarbonRange::create([
                    'start' => Carbon::now()->addHours(12),
                    'end' => Carbon::now()->addHours(13),
                ]),
            ], $faker),
            $this->generateDwsHomeHelpServiceChunk([
                'userId' => $this->users[0]->id,
                'buildingType' => DwsHomeHelpServiceBuildingType::none(),
                'isFirst' => true,
            ], $faker),
            $this->generateDwsHomeHelpServiceChunk([
                'userId' => $this->users[0]->id,
                'buildingType' => DwsHomeHelpServiceBuildingType::none(),
                'isWelfareSpecialistCooperation' => true,
            ], $faker),
        ];
    }

    /**
     * 障害福祉サービス請求：サービス単位（居宅介護） データ生成.
     *
     * @param array $overwrites
     * @param \Faker\Generator $faker
     * @return \Domain\Billing\DwsHomeHelpServiceChunkImpl
     */
    private function generateDwsHomeHelpServiceChunk(array $overwrites, Generator $faker): DwsHomeHelpServiceChunkImpl
    {
        $attrs = [
            'category' => $faker->randomElement(DwsServiceCodeCategory::all()),
            'buildingType' => $faker->randomElement(DwsHomeHelpServiceBuildingType::all()),
            'isEmergency' => $faker->boolean,
            'isPlannedByNovice' => $faker->boolean,
            'isFirst' => $faker->boolean,
            'isWelfareSpecialistCooperation' => $faker->boolean,
            'range' => CarbonRange::create([
                'start' => Carbon::now(),
                'end' => Carbon::now()->addMinute(),
            ]),
            'fragments' => Seq::from(
                DwsHomeHelpServiceFragment::create([
                    'providerType' => $faker->randomElement(DwsHomeHelpServiceProviderType::all()),
                    'isSecondary' => $faker->boolean,
                    'range' => CarbonRange::create([
                        'start' => Carbon::now(),
                        'end' => Carbon::now()->addMinute(),
                    ]),
                    'headcount' => $faker->randomElement([1, 2]),
                ]),
            ),
        ];

        return DwsHomeHelpServiceChunkImpl::create($overwrites + $attrs);
    }
}
