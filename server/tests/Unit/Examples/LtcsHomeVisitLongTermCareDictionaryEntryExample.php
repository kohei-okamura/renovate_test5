<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Examples;

use Domain\Common\Carbon;
use Domain\Common\IntRange;
use Domain\Office\HomeVisitLongTermCareSpecifiedOfficeAddition;
use Domain\ServiceCode\ServiceCode;
use Domain\ServiceCodeDictionary\LtcsCalcCycle;
use Domain\ServiceCodeDictionary\LtcsCalcExtraScore;
use Domain\ServiceCodeDictionary\LtcsCalcScore;
use Domain\ServiceCodeDictionary\LtcsCalcType;
use Domain\ServiceCodeDictionary\LtcsCompositionType;
use Domain\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionaryEntry;
use Domain\ServiceCodeDictionary\LtcsNoteRequirement;
use Domain\ServiceCodeDictionary\LtcsServiceCodeCategory;
use Domain\ServiceCodeDictionary\Timeframe;
use Faker\Generator as FakerGenerator;
use Tests\Unit\Faker\Faker;

/**
 * {@link \Domain\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionaryEntry} Examples.
 *
 * @property-read \Domain\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionaryEntry[] $ltcsHomeVisitLongTermCareDictionaryEntries
 * @mixin \Tests\Unit\Examples\LtcsHomeVisitLongTermCareDictionaryExample
 */
trait LtcsHomeVisitLongTermCareDictionaryEntryExample
{
    /**
     * 介護保険サービス：訪問介護：サービスコード辞書エントリを生成する.
     *
     * @param \Faker\Generator $faker
     * @param array $attrs
     * @return \Domain\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionaryEntry
     */
    public function generateLtcsHomeVisitLongTermCareDictionaryEntry(
        FakerGenerator $faker,
        array $attrs
    ): LtcsHomeVisitLongTermCareDictionaryEntry {
        $values = [
            'createdAt' => Carbon::instance($faker->dateTime('2021-01-25')),
            'updatedAt' => Carbon::instance($faker->dateTime('2021-01-25')),
        ];
        return LtcsHomeVisitLongTermCareDictionaryEntry::create($attrs + $values);
    }

    /**
     * 介護保険サービス：訪問介護：サービスコード辞書エントリの一覧を生成する.
     *
     * @return array|\Domain\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionaryEntry[]
     */
    protected function ltcsHomeVisitLongTermCareDictionaryEntries(): array
    {
        $faker = Faker::make(1899178394);
        return [
            $this->generateLtcsHomeVisitLongTermCareDictionaryEntry($faker, [
                'id' => 1,
                'dictionaryId' => $this->ltcsHomeVisitLongTermCareDictionaries[2]->id,
                'serviceCode' => ServiceCode::fromString('111111'),
                'name' => '身体介護1',
                'category' => LtcsServiceCodeCategory::physicalCare(),
                'headcount' => 1,
                'compositionType' => LtcsCompositionType::basic(),
                'specifiedOfficeAddition' => HomeVisitLongTermCareSpecifiedOfficeAddition::none(),
                'noteRequirement' => LtcsNoteRequirement::none(),
                'isLimited' => true,
                'isBulkSubtractionTarget' => true,
                'isSymbioticSubtractionTarget' => true,
                'score' => LtcsCalcScore::create([
                    'value' => 249,
                    'calcType' => LtcsCalcType::score(),
                    'calcCycle' => LtcsCalcCycle::perService(),
                ]),
                'extraScore' => LtcsCalcExtraScore::create([
                    'isAvailable' => false,
                    'baseMinutes' => 0,
                    'unitScore' => 0,
                    'unitMinutes' => 0,
                    'specifiedOfficeAdditionCoefficient' => 0,
                    'timeframeAdditionCoefficient' => 0,
                ]),
                'timeframe' => Timeframe::daytime(),
                'totalMinutes' => IntRange::create(['start' => 20, 'end' => 30]),
                'physicalMinutes' => IntRange::create(['start' => 20, 'end' => 30]),
                'houseworkMinutes' => IntRange::create(['start' => 10, 'end' => 20]),
            ]),
            $this->generateLtcsHomeVisitLongTermCareDictionaryEntry($faker, [
                'id' => 2,
                'dictionaryId' => $this->ltcsHomeVisitLongTermCareDictionaries[2]->id,
                'serviceCode' => ServiceCode::fromString('112444'),
                'name' => '身9生3・2人・深・Ⅰ',
                'category' => LtcsServiceCodeCategory::physicalCareAndHousework(),
                'headcount' => 2,
                'compositionType' => LtcsCompositionType::composed(),
                'specifiedOfficeAddition' => HomeVisitLongTermCareSpecifiedOfficeAddition::addition1(),
                'noteRequirement' => LtcsNoteRequirement::durationMinutes(),
                'isLimited' => true,
                'isBulkSubtractionTarget' => true,
                'isSymbioticSubtractionTarget' => true,
                'score' => LtcsCalcScore::create([
                    'value' => 1273,
                    'calcType' => LtcsCalcType::baseScore(),
                    'calcCycle' => LtcsCalcCycle::perService(),
                ]),
                'extraScore' => LtcsCalcExtraScore::create([
                    'isAvailable' => true,
                    'baseMinutes' => 240,
                    'unitScore' => 83,
                    'unitMinutes' => 30,
                    'specifiedOfficeAdditionCoefficient' => 120,
                    'timeframeAdditionCoefficient' => 150,
                ]),
                'timeframe' => Timeframe::midnight(),
                'totalMinutes' => IntRange::create(['start' => 310, 'end' => 9999]),
                'physicalMinutes' => IntRange::create(['start' => 240, 'end' => 9999]),
                'houseworkMinutes' => IntRange::create(['start' => 70, 'end' => 9999]),
            ]),
            $this->generateLtcsHomeVisitLongTermCareDictionaryEntry($faker, [
                'id' => 3,
                'dictionaryId' => $this->ltcsHomeVisitLongTermCareDictionaries[3]->id,
                'serviceCode' => ServiceCode::fromString('116275'),
                'name' => '訪問介護処遇改善加算Ⅰ',
                'category' => LtcsServiceCodeCategory::treatmentImprovementAddition1(),
                'headcount' => 0,
                'compositionType' => LtcsCompositionType::independent(),
                'specifiedOfficeAddition' => HomeVisitLongTermCareSpecifiedOfficeAddition::none(),
                'noteRequirement' => LtcsNoteRequirement::none(),
                'isLimited' => false,
                'isBulkSubtractionTarget' => false,
                'isSymbioticSubtractionTarget' => false,
                'score' => LtcsCalcScore::create([
                    'value' => 137,
                    'calcType' => LtcsCalcType::permille(),
                    'calcCycle' => LtcsCalcCycle::perMonth(),
                ]),
                'extraScore' => LtcsCalcExtraScore::create([
                    'isAvailable' => false,
                    'baseMinutes' => 0,
                    'unitScore' => 0,
                    'unitMinutes' => 0,
                    'specifiedOfficeAdditionCoefficient' => 0,
                    'timeframeAdditionCoefficient' => 0,
                ]),
                'timeframe' => Timeframe::unknown(),
                'totalMinutes' => IntRange::create(['start' => 0, 'end' => 0]),
                'physicalMinutes' => IntRange::create(['start' => 0, 'end' => 0]),
                'houseworkMinutes' => IntRange::create(['start' => 0, 'end' => 0]),
            ]),
            $this->generateLtcsHomeVisitLongTermCareDictionaryEntry($faker, [
                'id' => 4,
                'dictionaryId' => $this->ltcsHomeVisitLongTermCareDictionaries[3]->id,
                'serviceCode' => ServiceCode::fromString('118300'),
                'name' => '訪問介護令和3年9月30日までの上乗せ分',
                'category' => LtcsServiceCodeCategory::covid19PandemicSpecialAddition(),
                'headcount' => 0,
                'compositionType' => LtcsCompositionType::independent(),
                'specifiedOfficeAddition' => HomeVisitLongTermCareSpecifiedOfficeAddition::none(),
                'noteRequirement' => LtcsNoteRequirement::none(),
                'isLimited' => false,
                'isBulkSubtractionTarget' => false,
                'isSymbioticSubtractionTarget' => false,
                'score' => LtcsCalcScore::create([
                    'value' => 137,
                    'calcType' => LtcsCalcType::permille(),
                    'calcCycle' => LtcsCalcCycle::perMonth(),
                ]),
                'extraScore' => LtcsCalcExtraScore::create([
                    'isAvailable' => false,
                    'baseMinutes' => 0,
                    'unitScore' => 0,
                    'unitMinutes' => 0,
                    'specifiedOfficeAdditionCoefficient' => 0,
                    'timeframeAdditionCoefficient' => 0,
                ]),
                'timeframe' => Timeframe::unknown(),
                'totalMinutes' => IntRange::create(['start' => 0, 'end' => 0]),
                'physicalMinutes' => IntRange::create(['start' => 0, 'end' => 0]),
                'houseworkMinutes' => IntRange::create(['start' => 0, 'end' => 0]),
            ]),
            $this->generateLtcsHomeVisitLongTermCareDictionaryEntry($faker, [
                'id' => 5,
                'dictionaryId' => $this->ltcsHomeVisitLongTermCareDictionaries[2]->id,
                'serviceCode' => ServiceCode::fromString('111213'),
                'name' => '身体介護2・深',
                'category' => LtcsServiceCodeCategory::physicalCareAndHousework(),
                'headcount' => 1,
                'compositionType' => LtcsCompositionType::composed(),
                'specifiedOfficeAddition' => HomeVisitLongTermCareSpecifiedOfficeAddition::none(),
                'noteRequirement' => LtcsNoteRequirement::none(),
                'isLimited' => true,
                'isBulkSubtractionTarget' => true,
                'isSymbioticSubtractionTarget' => true,
                'score' => LtcsCalcScore::create([
                    'value' => 593,
                    'calcType' => LtcsCalcType::score(),
                    'calcCycle' => LtcsCalcCycle::perService(),
                ]),
                'extraScore' => LtcsCalcExtraScore::create([
                    'isAvailable' => false,
                    'baseMinutes' => 0,
                    'unitScore' => 0,
                    'unitMinutes' => 0,
                    'specifiedOfficeAdditionCoefficient' => 0,
                    'timeframeAdditionCoefficient' => 0,
                ]),
                'timeframe' => Timeframe::midnight(),
                'totalMinutes' => IntRange::create(['start' => 30, 'end' => 60]),
                'physicalMinutes' => IntRange::create(['start' => 30, 'end' => 60]),
                'houseworkMinutes' => IntRange::create(['start' => 0, 'end' => 0]),
            ]),
            $this->generateLtcsHomeVisitLongTermCareDictionaryEntry($faker, [
                'id' => 6,
                'dictionaryId' => $this->ltcsHomeVisitLongTermCareDictionaries[2]->id,
                'serviceCode' => ServiceCode::fromString('114001'),
                'name' => '訪問介護初回加算',
                'category' => LtcsServiceCodeCategory::firstTimeAddition(),
                'headcount' => 1,
                'compositionType' => LtcsCompositionType::independent(),
                'specifiedOfficeAddition' => HomeVisitLongTermCareSpecifiedOfficeAddition::none(),
                'noteRequirement' => LtcsNoteRequirement::none(),
                'isLimited' => true,
                'isBulkSubtractionTarget' => false,
                'isSymbioticSubtractionTarget' => false,
                'score' => LtcsCalcScore::create([
                    'value' => 200,
                    'calcType' => LtcsCalcType::score(),
                    'calcCycle' => LtcsCalcCycle::perMonth(),
                ]),
                'extraScore' => LtcsCalcExtraScore::create([
                    'isAvailable' => false,
                    'baseMinutes' => 0,
                    'unitScore' => 0,
                    'unitMinutes' => 0,
                    'specifiedOfficeAdditionCoefficient' => 0,
                    'timeframeAdditionCoefficient' => 0,
                ]),
                'timeframe' => Timeframe::unknown(),
                'totalMinutes' => IntRange::create(['start' => 0, 'end' => 0]),
                'physicalMinutes' => IntRange::create(['start' => 0, 'end' => 0]),
                'houseworkMinutes' => IntRange::create(['start' => 0, 'end' => 0]),
            ]),
        ];
    }
}
