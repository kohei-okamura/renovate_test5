<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Examples;

use Domain\Calling\Calling;
use Domain\Common\Carbon;
use Faker\Generator;

/**
 * Calling Example.
 *
 * @property-read \Domain\Calling\Calling[] $callings
 * @mixin \Tests\Unit\Examples\StaffExample
 * @mixin \Tests\Unit\Examples\ShiftExample
 */
trait CallingExample
{
    /**
     * 出勤確認の一覧を生成する.
     *
     * @return \Domain\Calling\Calling[]
     */
    protected function callings(): array
    {
        return [
            $this->generateCalling([
                'id' => 1,
                'staffId' => $this->staffs[0]->id,
                'shiftIds' => [
                    $this->shifts[0]->id,
                    $this->shifts[1]->id,
                ],
                'expiredAt' => Carbon::now()->microsecond(0)->addMinute(),
            ]),
            $this->generateCalling([
                'id' => 2,
                'staffId' => $this->staffs[4]->id,
                'shiftIds' => [
                    $this->shifts[2]->id,
                    $this->shifts[3]->id,
                ],
            ]),
            $this->generateCalling([
                'id' => 3,
                'staffId' => $this->staffs[0]->id,
                'shiftIds' => [
                    $this->shifts[3]->id,
                ],
                'expiredAt' => Carbon::now()->microsecond(0)->subMinute(),
            ]),
            $this->generateCalling([
                'id' => 4,
                'staffId' => $this->staffs[0]->id,
                'shiftIds' => [
                ],
                'expiredAt' => Carbon::now()->microsecond(0)->addMinute(),
            ]),
        ];
    }

    /**
     * Calling instance を生成する.
     *
     * @param array $overwrites
     * @return \Domain\Calling\Calling
     */
    protected function generateCalling(array $overwrites)
    {
        /** @var \Faker\Generator $faker */
        $faker = app(Generator::class);
        $attrs = [
            'token' => $faker->text(60),
            'expiredAt' => Carbon::now()->microsecond(0),
            'createdAt' => Carbon::instance($faker->dateTime),
        ];
        return Calling::create($overwrites + $attrs);
    }
}
