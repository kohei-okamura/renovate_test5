<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\ServiceCodeDictionary;

/**
 * Support functions for {@link \Domain\ServiceCodeDictionary\Timeframe}.
 *
 * @mixin \Domain\ServiceCodeDictionary\Timeframe
 */
trait TimeframeSupport
{
    /**
     * 「時」から時間帯を取得する.
     *
     * @param int $hour
     * @return static
     */
    public static function fromHour(int $hour): self
    {
        if ($hour < TimeframeConstants::START_OF_MORNING) {
            return self::midnight();
        } elseif ($hour < TimeframeConstants::START_OF_DAYTIME) {
            return self::morning();
        } elseif ($hour < TimeframeConstants::START_OF_NIGHT) {
            return self::daytime();
        } elseif ($hour < TimeframeConstants::START_OF_MIDNIGHT) {
            return self::night();
        } else {
            return self::midnight();
        }
    }

    /**
     * 次に境界となる「時」を取得する.
     *
     * @param int $hour
     * @return int
     */
    public static function getNextBoundaryHour(int $hour): int
    {
        if ($hour < TimeframeConstants::START_OF_MORNING) {
            return TimeframeConstants::START_OF_MORNING;
        } elseif ($hour < TimeframeConstants::START_OF_DAYTIME) {
            return TimeframeConstants::START_OF_DAYTIME;
        } elseif ($hour < TimeframeConstants::START_OF_NIGHT) {
            return TimeframeConstants::START_OF_NIGHT;
        } elseif ($hour < TimeframeConstants::START_OF_MIDNIGHT) {
            return TimeframeConstants::START_OF_MIDNIGHT;
        } else {
            return TimeframeConstants::START_OF_DAY;
        }
    }

    /**
     * 次の時間帯を返す.
     *
     * @return self
     */
    public function next(): self
    {
        switch ($this) {
            case self::midnight():
                return self::morning();
            case self::morning():
                return self::daytime();
            case self::daytime():
                return self::night();
            case self::night():
                return self::midnight();
            default:
                return self::unknown();
        }
    }

    /**
     * 時間帯の終端となる時刻の「時」を返す.
     *
     * @return int
     */
    public function nextBoundaryHour(): int
    {
        switch ($this) {
            case self::midnight():
                return TimeframeConstants::START_OF_MORNING;
            case self::morning():
                return TimeframeConstants::START_OF_DAYTIME;
            case self::daytime():
                return TimeframeConstants::START_OF_NIGHT;
            case self::night():
                return TimeframeConstants::START_OF_MIDNIGHT;
            default:
                return TimeframeConstants::START_OF_DAY;
        }
    }
}
