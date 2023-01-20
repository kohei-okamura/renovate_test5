<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Common;

use ScalikePHP\Option;

/**
 * Carbon Range.
 *
 * @property-read \Domain\Common\Carbon $start
 * @property-read \Domain\Common\Carbon $end
 */
final class CarbonRange extends Range
{
    /**
     * 月初から月末までの範囲を得る.
     *
     * @param \Domain\Common\Carbon $month
     * @return static
     */
    public static function ofMonth(Carbon $month): self
    {
        return self::create([
            'start' => $month->startOfMonth(),
            'end' => $month->endOfMonth(),
        ]);
    }

    /**
     * 範囲の時間量（分）を返す.
     *
     * @return int
     */
    public function durationMinutes(): int
    {
        return $this->start->diffInMinutes($this->end);
    }

    /**
     * 範囲が連続しているかどうかを判定する.
     *
     * @param \Domain\Common\CarbonRange $that
     * @return bool
     */
    public function isConsecutive(self $that): bool
    {
        return $this->end->eq($that->start) || $that->end->eq($this->start);
    }

    /**
     * 重複している範囲を返す.
     *
     * @param \Domain\Common\CarbonRange $that
     * @return \Domain\Common\CarbonRange[]|\ScalikePHP\Option
     */
    public function intersection(self $that): Option
    {
        $start = max($this->start, $that->start);
        $end = min($this->end, $that->end);
        return $start >= $end
            ? Option::none()
            : Option::some(CarbonRange::create(['start' => $start, 'end' => $end]));
    }
}
