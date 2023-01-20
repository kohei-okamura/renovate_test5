<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Common;

use Domain\Common\Carbon;
use Domain\Common\CarbonRange;

/**
 * {@link \Domain\Common\CarbonRange} Holder.
 *
 * @property-read \Domain\Common\CarbonRange $period 適用期間
 * @mixin \Eloquent
 */
trait PeriodHolder
{
    /**
     * Get mutator from period.
     *
     * @return \Domain\Common\CarbonRange
     * @noinspection PhpUnused
     */
    protected function getPeriodAttribute(): CarbonRange
    {
        return CarbonRange::create([
            'start' => Carbon::parse($this->attributes['period_start']),
            'end' => Carbon::parse($this->attributes['period_end']),
        ]);
    }

    /**
     * Set Mutator for attributes.
     *
     * @param \Domain\Common\CarbonRange $carbonRange
     * @noinspection PhpUnused
     */
    protected function setPeriodAttribute(CarbonRange $carbonRange): void
    {
        $this->attributes['period_start'] = $carbonRange->start;
        $this->attributes['period_end'] = $carbonRange->end;
    }
}
