<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Examples;

use Domain\Calling\CallingResponse;
use Domain\Common\Carbon;
use Faker\Generator;

/**
 * CallingResponse Example.
 *
 * @property-read \Domain\Calling\CallingResponse[] $callingResponses
 * @mixin \Tests\Unit\Examples\CallingExample
 */
trait CallingResponseExample
{
    /**
     * 出勤確認応答の一覧を生成する.
     *
     * @return \Domain\Calling\CallingResponse[]
     */
    protected function callingResponses(): array
    {
        return [
            $this->generateCallingResponse([
                'id' => 1,
                'callingId' => $this->callings[0]->id,
            ]),
            $this->generateCallingResponse([
                'id' => 2,
                'callingId' => $this->callings[1]->id,
            ]),
            $this->generateCallingResponse([
                'id' => 3,
                'callingId' => $this->callings[2]->id,
            ]),
        ];
    }

    /**
     * CallingResponse インスタンスを生成する.
     *
     * @param array $overwrites
     * @return \Domain\Calling\CallingResponse
     */
    protected function generateCallingResponse(array $overwrites): CallingResponse
    {
        $faker = app(Generator::class);
        $attrs = [
            'createdAt' => Carbon::instance($faker->dateTime),
        ];
        return CallingResponse::create($overwrites + $attrs);
    }
}
