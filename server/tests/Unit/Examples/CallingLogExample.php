<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Examples;

use Domain\Calling\CallingLog;
use Domain\Calling\CallingType;
use Domain\Common\Carbon;
use Faker\Generator;

/**
 * CallingLog Example.
 *
 * @property-read \Domain\Calling\CallingLog[] $callingLogs
 * @mixin \Tests\Unit\Examples\CallingExample
 */
trait CallingLogExample
{
    /**
     * 契約の一覧を生成する.
     *
     * @return \Domain\Calling\CallingLog[]
     */
    protected function callingLogs(): array
    {
        return [
            $this->generateCallingLog([
                'id' => 1,
                'callingId' => $this->callings[0]->id,
            ]),
            $this->generateCallingLog([
                'id' => 2,
                'callingId' => $this->callings[1]->id,
            ]),
            $this->generateCallingLog([
                'id' => 3,
                'callingId' => $this->callings[2]->id,
            ]),
        ];
    }

    /**
     * CallingLog インスタンを生成する.
     *
     * @param array $overwrites
     * @return \Domain\Calling\CallingLog
     */
    protected function generateCallingLog(array $overwrites): CallingLog
    {
        $faker = app(Generator::class);
        $attrs = [
            'callingType' => $faker->randomElement(CallingType::all()),
            'isSucceeded' => $faker->boolean,
            'createdAt' => Carbon::instance($faker->dateTime),
        ];
        return CallingLog::create($overwrites + $attrs);
    }
}
