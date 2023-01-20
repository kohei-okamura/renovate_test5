<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\ServiceCodeDictionary;

use Domain\Common\Carbon;
use Domain\Common\IntRange;
use Domain\Csv\CsvRow;
use Domain\Office\HomeVisitLongTermCareSpecifiedOfficeAddition;
use Domain\ServiceCode\ServiceCode;

/**
 * 介護保険サービス：訪問介護：サービスコード辞書 CSV 行.
 */
final class LtcsHomeVisitLongTermCareDictionaryCsvRow extends CsvRow
{
    private const COLUMN_SERVICE_CODE = 0;
    private const COLUMN_NAME = 3;
    private const COLUMN_CATEGORY = 4;
    private const COLUMN_HEADCOUNT = 5;
    private const COLUMN_COMPOSITION_TYPE = 6;
    private const COLUMN_SPECIFIED_OFFICE_ADDITION = 7;
    private const COLUMN_NOTE_REQUIREMENT = 8;
    private const COLUMN_IS_LIMITED = 9;
    private const COLUMN_IS_BULK_SUBTRACTION_TARGET = 10;
    private const COLUMN_IS_SYMBIOTIC_SUBTRACTION_TARGET = 11;
    private const COLUMN_SCORE_VALUE = 12;
    private const COLUMN_SCORE_CALC_TYPE = 13;
    private const COLUMN_SCORE_CALC_CYCLE = 14;
    private const COLUMN_EXTRA_SCORE_IS_AVAILABLE = 15;
    private const COLUMN_EXTRA_SCORE_BASE_MINUTES = 16;
    private const COLUMN_EXTRA_SCORE_UNIT_SCORE = 17;
    private const COLUMN_EXTRA_SCORE_UNIT_MINUTES = 18;
    private const COLUMN_EXTRA_SCORE_SPECIFIED_OFFICE_ADDITION_COEFFICIENT = 19;
    private const COLUMN_EXTRA_SCORE_TIMEFRAME_ADDITION_COEFFICIENT = 20;
    private const COLUMN_TIMEFRAME = 21;
    private const COLUMN_TOTAL_MINUTES_START = 22;
    private const COLUMN_TOTAL_MINUTES_END = 23;
    private const COLUMN_PHYSICAL_MINUTES_START = 24;
    private const COLUMN_PHYSICAL_MINUTES_END = 25;
    private const COLUMN_HOUSEWORK_MINUTES_START = 26;
    private const COLUMN_HOUSEWORK_MINUTES_END = 27;

    /**
     * 介護保険サービス：訪問介護：サービスコード辞書エントリに変換する.
     *
     * @param array $attrs
     * @return \Domain\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionaryEntry
     */
    public function toDictionaryEntry(array $attrs): LtcsHomeVisitLongTermCareDictionaryEntry
    {
        $now = Carbon::now();
        $values = [
            'serviceCode' => ServiceCode::fromString($this->getString(self::COLUMN_SERVICE_CODE)),
            'name' => $this->getString(self::COLUMN_NAME),
            'category' => LtcsServiceCodeCategory::from($this->getInteger(self::COLUMN_CATEGORY)),
            'headcount' => $this->getInteger(self::COLUMN_HEADCOUNT),
            'compositionType' => LtcsCompositionType::from($this->getInteger(self::COLUMN_COMPOSITION_TYPE)),
            'specifiedOfficeAddition' => HomeVisitLongTermCareSpecifiedOfficeAddition::from(
                $this->getInteger(self::COLUMN_SPECIFIED_OFFICE_ADDITION)
            ),
            'noteRequirement' => LtcsNoteRequirement::from($this->getInteger(self::COLUMN_NOTE_REQUIREMENT)),
            'isLimited' => $this->getBoolean(self::COLUMN_IS_LIMITED),
            'isBulkSubtractionTarget' => $this->getBoolean(self::COLUMN_IS_BULK_SUBTRACTION_TARGET),
            'isSymbioticSubtractionTarget' => $this->getBoolean(self::COLUMN_IS_SYMBIOTIC_SUBTRACTION_TARGET),
            'score' => LtcsCalcScore::create([
                'value' => $this->getInteger(self::COLUMN_SCORE_VALUE),
                'calcType' => LtcsCalcType::from($this->getInteger(self::COLUMN_SCORE_CALC_TYPE)),
                'calcCycle' => LtcsCalcCycle::from($this->getInteger(self::COLUMN_SCORE_CALC_CYCLE)),
            ]),
            'extraScore' => LtcsCalcExtraScore::create([
                'isAvailable' => $this->getBoolean(self::COLUMN_EXTRA_SCORE_IS_AVAILABLE),
                'baseMinutes' => $this->getInteger(self::COLUMN_EXTRA_SCORE_BASE_MINUTES),
                'unitScore' => $this->getInteger(self::COLUMN_EXTRA_SCORE_UNIT_SCORE),
                'unitMinutes' => $this->getInteger(self::COLUMN_EXTRA_SCORE_UNIT_MINUTES),
                'specifiedOfficeAdditionCoefficient' => $this->getInteger(
                    self::COLUMN_EXTRA_SCORE_SPECIFIED_OFFICE_ADDITION_COEFFICIENT
                ),
                'timeframeAdditionCoefficient' => $this->getInteger(
                    self::COLUMN_EXTRA_SCORE_TIMEFRAME_ADDITION_COEFFICIENT
                ),
            ]),
            'timeframe' => Timeframe::from($this->getInteger(self::COLUMN_TIMEFRAME)),
            'totalMinutes' => IntRange::create([
                'start' => $this->getInteger(self::COLUMN_TOTAL_MINUTES_START),
                'end' => $this->getInteger(self::COLUMN_TOTAL_MINUTES_END),
            ]),
            'physicalMinutes' => IntRange::create([
                'start' => $this->getInteger(self::COLUMN_PHYSICAL_MINUTES_START),
                'end' => $this->getInteger(self::COLUMN_PHYSICAL_MINUTES_END),
            ]),
            'houseworkMinutes' => IntRange::create([
                'start' => $this->getInteger(self::COLUMN_HOUSEWORK_MINUTES_START),
                'end' => $this->getInteger(self::COLUMN_HOUSEWORK_MINUTES_END),
            ]),
            'createdAt' => $now,
            'updatedAt' => $now,
        ];
        return LtcsHomeVisitLongTermCareDictionaryEntry::create($attrs + $values);
    }
}
