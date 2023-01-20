<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Common;

use Domain\Common\Schedule;

/**
 * {@link \Domain\Common\Schedule} Holder.
 *
 * @property-read \Domain\Common\Schedule $schedule
 * @mixin \Eloquent
 */
trait ScheduleHolder
{
    /**
     * Get mutator for schedule.
     *
     * @return \Domain\Common\Schedule
     * @noinspection PhpUnused
     */
    protected function getScheduleAttribute(): Schedule
    {
        return Schedule::create([
            'start' => $this->schedule_start,
            'end' => $this->schedule_end,
            'date' => $this->schedule_date,
        ]);
    }

    /**
     * Set mutator for schedule.
     *
     * @param \Domain\Common\Schedule $schedule
     * @return void
     * @noinspection PhpUnused
     */
    protected function setScheduleAttribute(Schedule $schedule): void
    {
        $this->attributes['schedule_start'] = $schedule->start;
        $this->attributes['schedule_end'] = $schedule->end;
        $this->attributes['schedule_date'] = $schedule->date;
    }
}
