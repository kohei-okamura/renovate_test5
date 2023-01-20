<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Api\Attendance;

use Domain\Shift\Attendance;
use Tests\Api\Test;

/**
 * /attendances に関連するテストの基底クラス
 */
abstract class AttendanceTest extends Test
{
    /**
     * Example からリクエストパラメータを作る.
     *
     * @param \Domain\Shift\Attendance $example
     * @return array
     */
    protected function buildParamFromExample(Attendance $example): array
    {
        $schedule = [
            'date' => $example->schedule->date->toDateString(),
            'start' => $example->schedule->start->format('H:i'),
            'end' => $example->schedule->end->format('H:i'),
        ];
        return compact('schedule') + $this->domainToArray($example);
    }
}
