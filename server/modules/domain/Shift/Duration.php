<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Shift;

use Domain\Model;

/**
 * 勤務時間.
 *
 * @property-read \Domain\Shift\Activity $activity 勤務内容
 * @property-read int $duration 所要時間（分）
 */
final class Duration extends Model
{
    /** {@inheritdoc} */
    protected function attrs(): array
    {
        return [
            'activity',
            'duration',
        ];
    }

    /** {@inheritdoc} */
    protected function jsonables(): array
    {
        return [
            'activity' => true,
            'duration' => true,
        ];
    }
}
