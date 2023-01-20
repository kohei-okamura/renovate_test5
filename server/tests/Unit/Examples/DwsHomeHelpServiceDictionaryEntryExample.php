<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Examples;

use Domain\Common\Carbon;
use Domain\Common\IntRange;
use Domain\ServiceCode\ServiceCode;
use Domain\ServiceCodeDictionary\DwsHomeHelpServiceBuildingType;
use Domain\ServiceCodeDictionary\DwsHomeHelpServiceDictionaryEntry;
use Domain\ServiceCodeDictionary\DwsHomeHelpServiceProviderType;
use Domain\ServiceCodeDictionary\DwsServiceCodeCategory;
use Faker\Generator;

/**
 * DwsHomeHelpServiceDictionaryEntry Example.
 *
 * @property-read DwsHomeHelpServiceDictionaryEntry[] $dwsHomeHelpServiceDictionaryEntries
 * @mixin \Tests\Unit\Examples\DwsHomeHelpServiceDictionaryExample
 */
trait DwsHomeHelpServiceDictionaryEntryExample
{
    /**
     * 障害福祉サービス：居宅介護：サービスコード辞書エントリの一覧を生成する.
     *
     * @return DwsHomeHelpServiceDictionaryEntry[]
     */
    protected function dwsHomeHelpServiceDictionaryEntries(): array
    {
        return [
            $this->generateDwsHomeHelpServiceDictionaryEntry([
                'id' => 1,
                'dwsHomeHelpServiceDictionaryId' => $this->dwsHomeHelpServiceDictionaries[0]->id,
                'category' => DwsServiceCodeCategory::physicalCare(),
            ]),
            $this->generateDwsHomeHelpServiceDictionaryEntry([
                'id' => 2,
                'dwsHomeHelpServiceDictionaryId' => $this->dwsHomeHelpServiceDictionaries[0]->id,
                'category' => DwsServiceCodeCategory::physicalCare(),
            ]),
            $this->generateDwsHomeHelpServiceDictionaryEntry([
                'id' => 3,
                'dwsHomeHelpServiceDictionaryId' => $this->dwsHomeHelpServiceDictionaries[0]->id,
                'category' => DwsServiceCodeCategory::housework(),
                'isSecondary' => true,
            ]),
            $this->generateDwsHomeHelpServiceDictionaryEntry([
                'id' => 4,
                'dwsHomeHelpServiceDictionaryId' => $this->dwsHomeHelpServiceDictionaries[0]->id,
                'category' => DwsServiceCodeCategory::accompanyWithPhysicalCare(),
            ]),
            $this->generateDwsHomeHelpServiceDictionaryEntry([
                'id' => 5,
                'dwsHomeHelpServiceDictionaryId' => $this->dwsHomeHelpServiceDictionaries[0]->id,
                'category' => DwsServiceCodeCategory::accompany(),
            ]),
            $this->generateDwsHomeHelpServiceDictionaryEntry([
                'id' => 6,
                'dwsHomeHelpServiceDictionaryId' => $this->dwsHomeHelpServiceDictionaries[0]->id,
                'category' => DwsServiceCodeCategory::accessibleTaxi(),
                'isExtra' => false,
            ]),
            $this->generateDwsHomeHelpServiceDictionaryEntry([
                'id' => 7,
                'dwsHomeHelpServiceDictionaryId' => $this->dwsHomeHelpServiceDictionaries[0]->id,
                'category' => DwsServiceCodeCategory::accessibleTaxi(),
                'isSecondary' => false,
            ]),
            $this->generateDwsHomeHelpServiceDictionaryEntry([
                'id' => 8,
                'dwsHomeHelpServiceDictionaryId' => $this->dwsHomeHelpServiceDictionaries[0]->id,
                'category' => DwsServiceCodeCategory::firstTimeAddition(),
                'isPlannedByNovice' => false,
            ]),
            $this->generateDwsHomeHelpServiceDictionaryEntry([
                'id' => 9,
                'dwsHomeHelpServiceDictionaryId' => $this->dwsHomeHelpServiceDictionaries[0]->id,
                'category' => DwsServiceCodeCategory::emergencyAddition1(),
                'isPlannedByNovice' => true,
            ]),
            $this->generateDwsHomeHelpServiceDictionaryEntry([
                'id' => 10,
                'dwsHomeHelpServiceDictionaryId' => $this->dwsHomeHelpServiceDictionaries[0]->id,
                'category' => DwsServiceCodeCategory::suckingSupportSystemAddition(),
                'isExtra' => true,
            ]),
            $this->generateDwsHomeHelpServiceDictionaryEntry([
                'id' => 11,
                'dwsHomeHelpServiceDictionaryId' => $this->dwsHomeHelpServiceDictionaries[1]->id,
                'category' => DwsServiceCodeCategory::specifiedOfficeAddition1(),
            ]),
            $this->generateDwsHomeHelpServiceDictionaryEntry([
                'id' => 12,
                'dwsHomeHelpServiceDictionaryId' => $this->dwsHomeHelpServiceDictionaries[2]->id,
                'serviceCode' => ServiceCode::fromString('110901'),
                'name' => '架空加算',
                'category' => DwsServiceCodeCategory::treatmentImprovementAddition1(),
            ]),
            $this->generateDwsHomeHelpServiceDictionaryEntry([
                'id' => 13,
                'dwsHomeHelpServiceDictionaryId' => $this->dwsHomeHelpServiceDictionaries[0]->id,
                'category' => DwsServiceCodeCategory::copayCoordinationAddition(),
                'serviceCode' => ServiceCode::fromString('115010'),
                'name' => '居介上限額管理加算',
            ]),
            $this->generateDwsHomeHelpServiceDictionaryEntry([
                'id' => 14,
                'dwsHomeHelpServiceDictionaryId' => $this->dwsHomeHelpServiceDictionaries[2]->id,
                'serviceCode' => ServiceCode::fromString('111000'),
                'name' => '適当な居宅サービス',
            ]),
            $this->generateDwsHomeHelpServiceDictionaryEntry([
                'id' => 15,
                'dwsHomeHelpServiceDictionaryId' => $this->dwsHomeHelpServiceDictionaries[2]->id,
                'serviceCode' => ServiceCode::fromString('111111'),
                'name' => '身体日0.5',
                'daytimeDuration' => IntRange::create([
                    'start' => 1,
                    'end' => 2,
                ]),
                'morningDuration' => IntRange::create([
                    'start' => 1,
                    'end' => 2,
                ]),
                'nightDuration' => IntRange::create([
                    'start' => 1,
                    'end' => 2,
                ]),
                'midnightDuration1' => IntRange::create([
                    'start' => 1,
                    'end' => 2,
                ]),
                'midnightDuration2' => IntRange::create([
                    'start' => 1,
                    'end' => 2,
                ]),
            ]),
        ];
    }

    /**
     * テスト用のサービスコード辞書エントリ（障害：居宅介護）を生成する.
     *
     * @param array $overwrites
     * @return \Domain\ServiceCodeDictionary\DwsHomeHelpServiceDictionaryEntry
     */
    protected function generateDwsHomeHelpServiceDictionaryEntry(array $overwrites): DwsHomeHelpServiceDictionaryEntry
    {
        /** @var \Faker\Generator $faker */
        $faker = app(Generator::class);
        $attrs = [
            'serviceCode' => ServiceCode::fromString("119{$faker->unique()->randomNumber(3, true)}"),
            'name' => $faker->text(100),
            'category' => $faker->randomElement(DwsServiceCodeCategory::all()),
            'isExtra' => $faker->boolean,
            'isSecondary' => $faker->boolean,
            'providerType' => $faker->randomElement(DwsHomeHelpServiceProviderType::all()),
            'isPlannedByNovice' => $faker->boolean,
            'buildingType' => $faker->randomElement(DwsHomeHelpServiceBuildingType::all()),
            'score' => $faker->numberBetween(1, 10),
            'daytimeDuration' => IntRange::create([
                'start' => $faker->numberBetween(1, 3),
                'end' => $faker->numberBetween(4, 6),
            ]),
            'morningDuration' => IntRange::create([
                'start' => $faker->numberBetween(1, 3),
                'end' => $faker->numberBetween(4, 6),
            ]),
            'nightDuration' => IntRange::create([
                'start' => $faker->numberBetween(1, 3),
                'end' => $faker->numberBetween(4, 6),
            ]),
            'midnightDuration1' => IntRange::create([
                'start' => $faker->numberBetween(1, 3),
                'end' => $faker->numberBetween(4, 6),
            ]),
            'midnightDuration2' => IntRange::create([
                'start' => $faker->numberBetween(1, 3),
                'end' => $faker->numberBetween(4, 6),
            ]),
            'createdAt' => Carbon::instance($faker->dateTime),
            'updatedAt' => Carbon::instance($faker->dateTime),
        ];
        return DwsHomeHelpServiceDictionaryEntry::create($overwrites + $attrs);
    }
}
