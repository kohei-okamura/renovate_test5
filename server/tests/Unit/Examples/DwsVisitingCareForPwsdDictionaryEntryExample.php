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
use Domain\ServiceCodeDictionary\DwsServiceCodeCategory;
use Domain\ServiceCodeDictionary\DwsVisitingCareForPwsdDictionaryEntry;
use Domain\ServiceCodeDictionary\Timeframe;
use Faker\Generator;

/**
 * DwsVisitingCareForPwsdDictionaryEntry Example.
 *
 * @property-read DwsVisitingCareForPwsdDictionaryEntry[] $dwsVisitingCareForPwsdDictionaryEntries
 * @mixin \Tests\Unit\Examples\DwsVisitingCareForPwsdDictionaryExample
 */
trait DwsVisitingCareForPwsdDictionaryEntryExample
{
    /**
     * 障害福祉サービス：重度訪問介護：サービスコード辞書エントリの一覧を生成する.
     *
     * @return DwsVisitingCareForPwsdDictionaryEntry[]
     */
    protected function dwsVisitingCareForPwsdDictionaryEntries(): array
    {
        return [
            $this->generateDwsVisitingCareForPwsdDictionaryEntry([
                'id' => 1,
                'dwsVisitingCareForPwsdDictionaryId' => $this->dwsVisitingCareForPwsdDictionaries[0]->id,
                'category' => DwsServiceCodeCategory::visitingCareForPwsd1(),
            ]),
            $this->generateDwsVisitingCareForPwsdDictionaryEntry([
                'id' => 2,
                'dwsVisitingCareForPwsdDictionaryId' => $this->dwsVisitingCareForPwsdDictionaries[0]->id,
                'category' => DwsServiceCodeCategory::visitingCareForPwsd2(),
            ]),
            $this->generateDwsVisitingCareForPwsdDictionaryEntry([
                'id' => 3,
                'dwsVisitingCareForPwsdDictionaryId' => $this->dwsVisitingCareForPwsdDictionaries[0]->id,
                'category' => DwsServiceCodeCategory::visitingCareForPwsd2(),
                'isSecondary' => true,
            ]),
            $this->generateDwsVisitingCareForPwsdDictionaryEntry([
                'id' => 4,
                'dwsVisitingCareForPwsdDictionaryId' => $this->dwsVisitingCareForPwsdDictionaries[0]->id,
                'category' => DwsServiceCodeCategory::visitingCareForPwsd2(),
                'isCoaching' => true,
            ]),
            $this->generateDwsVisitingCareForPwsdDictionaryEntry([
                'id' => 5,
                'dwsVisitingCareForPwsdDictionaryId' => $this->dwsVisitingCareForPwsdDictionaries[0]->id,
                'category' => DwsServiceCodeCategory::visitingCareForPwsd3(),
                'timeframe' => Timeframe::night(),
                'isHospitalized' => true,
            ]),
            $this->generateDwsVisitingCareForPwsdDictionaryEntry([
                'id' => 6,
                'dwsVisitingCareForPwsdDictionaryId' => $this->dwsVisitingCareForPwsdDictionaries[0]->id,
                'category' => DwsServiceCodeCategory::visitingCareForPwsd2(),
                'isLongHospitalized' => true,
            ]),
            $this->generateDwsVisitingCareForPwsdDictionaryEntry([
                'id' => 7,
                'dwsVisitingCareForPwsdDictionaryId' => $this->dwsVisitingCareForPwsdDictionaries[0]->id,
                'category' => DwsServiceCodeCategory::visitingCareForPwsd2(),
                'timeframe' => Timeframe::daytime(),
                'isLongHospitalized' => true,
            ]),
            $this->generateDwsVisitingCareForPwsdDictionaryEntry([
                'id' => 8,
                'dwsVisitingCareForPwsdDictionaryId' => $this->dwsVisitingCareForPwsdDictionaries[1]->id,
                'category' => DwsServiceCodeCategory::visitingCareForPwsd2(),
            ]),
            $this->generateDwsVisitingCareForPwsdDictionaryEntry([
                'id' => 9,
                'dwsVisitingCareForPwsdDictionaryId' => $this->dwsVisitingCareForPwsdDictionaries[2]->id,
                'category' => DwsServiceCodeCategory::visitingCareForPwsd2(),
                'serviceCode' => ServiceCode::fromString('120901'),
            ]),
            $this->generateDwsVisitingCareForPwsdDictionaryEntry([
                'id' => 10,
                'dwsVisitingCareForPwsdDictionaryId' => $this->dwsVisitingCareForPwsdDictionaries[0]->id,
                'category' => DwsServiceCodeCategory::copayCoordinationAddition(),
                'serviceCode' => ServiceCode::fromString('125010'),
                'name' => '重訪上限額管理加算',
            ]),
            $this->generateDwsVisitingCareForPwsdDictionaryEntry([
                'id' => 11,
                'dwsVisitingCareForPwsdDictionaryId' => $this->dwsVisitingCareForPwsdDictionaries[2]->id,
                'category' => DwsServiceCodeCategory::copayCoordinationAddition(),
                'serviceCode' => ServiceCode::fromString('129627'),
            ]),
            $this->generateDwsVisitingCareForPwsdDictionaryEntry([
                'id' => 12,
                'dwsVisitingCareForPwsdDictionaryId' => $this->dwsVisitingCareForPwsdDictionaries[2]->id,
                'category' => DwsServiceCodeCategory::treatmentImprovementAddition2(),
            ]),
            $this->generateDwsVisitingCareForPwsdDictionaryEntry([
                'id' => 13,
                'dwsVisitingCareForPwsdDictionaryId' => $this->dwsVisitingCareForPwsdDictionaries[2]->id,
                'category' => DwsServiceCodeCategory::visitingCareForPwsd2(),
                'serviceCode' => ServiceCode::fromString('123000'),
            ]),
            $this->generateDwsVisitingCareForPwsdDictionaryEntry([
                'id' => 14,
                'dwsVisitingCareForPwsdDictionaryId' => $this->dwsVisitingCareForPwsdDictionaries[2]->id,
                'category' => DwsServiceCodeCategory::visitingCareForPwsd2(),
                'serviceCode' => ServiceCode::fromString('123456'),
            ]),
        ];
    }

    /**
     * 障害福祉サービス：重度訪問介護：サービスコード辞書エントリを生成する.
     *
     * @param array $overwrites
     * @return \Domain\ServiceCodeDictionary\DwsVisitingCareForPwsdDictionaryEntry
     */
    protected function generateDwsVisitingCareForPwsdDictionaryEntry(
        array $overwrites
    ): DwsVisitingCareForPwsdDictionaryEntry {
        /** @var \Faker\Generator $faker */
        $faker = app(Generator::class);
        $attrs = [
            'serviceCode' => ServiceCode::fromString('129' . $faker->numerify('###')),
            'name' => $faker->word,
            'isSecondary' => false,
            'isCoaching' => false,
            'isHospitalized' => false,
            'isLongHospitalized' => false,
            'score' => $faker->numberBetween(1, 1000),
            'timeframe' => $faker->randomElement(Timeframe::all()),
            'duration' => IntRange::create([
                'start' => $faker->numberBetween(1, 3),
                'end' => $faker->numberBetween(4, 6),
            ]),
            'unit' => $faker->randomElement([0, 30, 60]),
            'createdAt' => Carbon::instance($faker->dateTime),
            'updatedAt' => Carbon::instance($faker->dateTime),
        ];
        return DwsVisitingCareForPwsdDictionaryEntry::create($overwrites + $attrs);
    }
}
