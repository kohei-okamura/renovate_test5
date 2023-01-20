<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Common;

use Domain\Model;

/**
 * スケジュール.
 *
 * @property-read \Domain\Common\Carbon $date
 * @property-read \Domain\Common\Carbon $start
 * @property-read \Domain\Common\Carbon $end
 */
final class Schedule extends Model
{
    /**
     * {@link \Domain\Common\CarbonRange} に変換する.
     *
     * @return \Domain\Common\CarbonRange
     */
    public function toRange(): CarbonRange
    {
        return CarbonRange::create(['start' => $this->start, 'end' => $this->end]);
    }

    /** {@inheritdoc} */
    protected function attrs(): array
    {
        return [
            'date',
            'start',
            'end',
        ];
    }

    /** {@inheritdoc} */
    protected function jsonables(): array
    {
        return [
            'date' => 'date',
            'start' => true,
            'end' => true,
        ];
    }
}
