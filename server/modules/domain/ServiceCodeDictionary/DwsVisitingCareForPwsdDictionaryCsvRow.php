<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\ServiceCodeDictionary;

use Domain\Common\Carbon;
use Domain\Common\IntRange;
use Domain\Csv\CsvRow;
use Domain\ServiceCode\ServiceCode;

/**
 * 障害福祉サービス：重度訪問介護：サービスコード辞書 CSV 行クラス.
 */
final class DwsVisitingCareForPwsdDictionaryCsvRow extends CsvRow
{
    private const COLUMN_SERVICE_CODE = 0;
    private const COLUMN_NAME = 3;
    private const COLUMN_CATEGORY = 4;
    private const COLUMN_IS_SECONDARY = 5;
    private const COLUMN_IS_COACHING = 6;
    private const COLUMN_IS_HOSPITALIZED = 7;
    private const COLUMN_IS_LONG_HOSPITALIZED = 8;
    private const COLUMN_SCORE = 9;
    private const COLUMN_TIMEFRAME = 10;
    private const COLUMN_DURATION_START = 11;
    private const COLUMN_DURATION_END = 12;
    private const COLUMN_UNIT = 13;

    /**
     * 障害福祉サービス：重度訪問介護：サービスコード辞書エントリに変換する.
     *
     * @param array $attrs
     * @return \Domain\ServiceCodeDictionary\DwsVisitingCareForPwsdDictionaryEntry
     */
    public function toDictionaryEntry(array $attrs): DwsVisitingCareForPwsdDictionaryEntry
    {
        $now = Carbon::now();
        $values = [
            'serviceCode' => ServiceCode::fromString($this->getString(self::COLUMN_SERVICE_CODE)),
            'name' => $this->getString(self::COLUMN_NAME),
            'category' => DwsServiceCodeCategory::from($this->getInteger(self::COLUMN_CATEGORY)),
            'isSecondary' => $this->getBoolean(self::COLUMN_IS_SECONDARY),
            'isCoaching' => $this->getBoolean(self::COLUMN_IS_COACHING),
            'isHospitalized' => $this->getBoolean(self::COLUMN_IS_HOSPITALIZED),
            'isLongHospitalized' => $this->getBoolean(self::COLUMN_IS_LONG_HOSPITALIZED),
            'score' => $this->getInteger(self::COLUMN_SCORE),
            'timeframe' => Timeframe::from($this->getInteger(self::COLUMN_TIMEFRAME)),
            'duration' => IntRange::create([
                'start' => $this->getInteger(self::COLUMN_DURATION_START),
                'end' => $this->getInteger(self::COLUMN_DURATION_END),
            ]),
            'unit' => $this->getInteger(self::COLUMN_UNIT),
            'createdAt' => $now,
            'updatedAt' => $now,
        ];
        return DwsVisitingCareForPwsdDictionaryEntry::create($attrs + $values);
    }
}
