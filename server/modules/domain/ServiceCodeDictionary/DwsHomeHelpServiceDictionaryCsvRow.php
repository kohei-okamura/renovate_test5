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
use ScalikePHP\Seq;

/**
 * 障害福祉サービス：居宅介護：サービスコード辞書 CSV 行クラス.
 */
final class DwsHomeHelpServiceDictionaryCsvRow extends CsvRow
{
    public const COLUMN_SERVICE_CODE = 0;
    public const COLUMN_NAME = 3;
    public const COLUMN_CATEGORY = 4;
    public const COLUMN_IS_EXTRA = 5;
    public const COLUMN_IS_SECONDARY = 6;
    public const COLUMN_PROVIDER_TYPE = 7;
    public const COLUMN_IS_PLANNED_BY_NOVICE = 8;
    public const COLUMN_BUILDING_TYPE = 9;
    public const COLUMN_SCORE = 10;
    public const COLUMN_TIMEFRAME1 = 11;
    public const COLUMN_DURATION1_START = 12;
    public const COLUMN_DURATION1_END = 13;
    public const COLUMN_TIMEFRAME2 = 14;
    public const COLUMN_DURATION2_START = 15;
    public const COLUMN_DURATION2_END = 16;
    public const COLUMN_TIMEFRAME3 = 17;
    public const COLUMN_DURATION3_START = 18;
    public const COLUMN_DURATION3_END = 19;

    /**
     * 障害福祉サービス：居宅介護：サービスコード辞書エントリに変換する.
     *
     * @param array $attrs
     * @return \Domain\ServiceCodeDictionary\DwsHomeHelpServiceDictionaryEntry
     */
    public function toDictionaryEntry(array $attrs): DwsHomeHelpServiceDictionaryEntry
    {
        $durations = $this->durations();
        $now = Carbon::now();
        $values = [
            'serviceCode' => ServiceCode::fromString($this->getString(self::COLUMN_SERVICE_CODE)),
            'name' => $this->getString(self::COLUMN_NAME),
            'category' => DwsServiceCodeCategory::from($this->getInteger(self::COLUMN_CATEGORY)),
            'isExtra' => $this->getBoolean(self::COLUMN_IS_EXTRA),
            'isSecondary' => $this->getBoolean(self::COLUMN_IS_SECONDARY),
            'providerType' => DwsHomeHelpServiceProviderType::from($this->getInteger(self::COLUMN_PROVIDER_TYPE)),
            'isPlannedByNovice' => $this->getBoolean(self::COLUMN_IS_PLANNED_BY_NOVICE),
            'buildingType' => DwsHomeHelpServiceBuildingType::from($this->getInteger(self::COLUMN_BUILDING_TYPE)),
            'score' => $this->getInteger(self::COLUMN_SCORE),
            'daytimeDuration' => $this->identifyDuration($durations, Timeframe::daytime()),
            'morningDuration' => $this->identifyDuration($durations, Timeframe::morning()),
            'nightDuration' => $this->identifyDuration($durations, Timeframe::night()),
            'midnightDuration1' => $this->identifyDuration($durations, Timeframe::midnight()),
            'midnightDuration2' => $this->isDaySpanning($durations)
                ? $durations[1]['range']
                : IntRange::create(['start' => 0, 'end' => 0]),
            'createdAt' => $now,
            'updatedAt' => $now,
        ];
        return DwsHomeHelpServiceDictionaryEntry::create($attrs + $values);
    }

    /**
     * 時間数情報を取得する.
     *
     * @return array[]|\ScalikePHP\Seq
     */
    private function durations(): Seq
    {
        $xs = Seq::from(
            [self::COLUMN_TIMEFRAME1, self::COLUMN_DURATION1_START, self::COLUMN_DURATION1_END],
            [self::COLUMN_TIMEFRAME2, self::COLUMN_DURATION2_START, self::COLUMN_DURATION2_END],
            [self::COLUMN_TIMEFRAME3, self::COLUMN_DURATION3_START, self::COLUMN_DURATION3_END],
        );
        return $xs->map(fn (array $x): array => [
            'timeframe' => Timeframe::from($this->getInteger($x[0])),
            'range' => IntRange::create(['start' => $this->getInteger($x[1]), 'end' => $this->getInteger($x[2])]),
        ]);
    }

    /**
     * 指定した時間帯に対応する時間数情報を特定する.
     *
     * @param array[]|\ScalikePHP\Seq $durations
     * @param \Domain\ServiceCodeDictionary\Timeframe $timeframe
     * @return \Domain\Common\IntRange
     */
    private function identifyDuration(Seq $durations, Timeframe $timeframe): IntRange
    {
        return $durations
            ->find(fn (array $x): bool => $x['timeframe'] === $timeframe)
            ->map(fn (array $x): IntRange => $x['range'])
            ->getOrElse(fn (): IntRange => IntRange::create(['start' => 0, 'end' => 0]));
    }

    /**
     * 日跨ぎかどうかを判定する.
     *
     * @param \ScalikePHP\Seq $durations
     * @return bool
     */
    private function isDaySpanning(Seq $durations): bool
    {
        return $durations[0]['timeframe'] === Timeframe::midnight()
            && $durations[1]['timeframe'] === Timeframe::midnight();
    }
}
