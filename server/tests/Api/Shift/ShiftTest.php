<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Api\Shift;

use Domain\Shift\Shift;
use Tests\Api\Test;

/**
 * /shifts に関連するテストの基底クラス
 */
abstract class ShiftTest extends Test
{
    /**
     * Example からリクエストパラメータを作る.
     *
     * @param \Domain\Shift\Shift $example
     * @return array
     */
    protected function buildParamFromExample(Shift $example): array
    {
        $schedule = [
            'date' => $example->schedule->date->toDateString(),
            'start' => $example->schedule->start->format('H:i'),
            'end' => $example->schedule->end->format('H:i'),
        ];
        return compact('schedule') + $this->domainToArray($example);
    }
}
