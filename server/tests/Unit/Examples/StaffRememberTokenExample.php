<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Examples;

use Domain\Common\Carbon;
use Domain\Staff\StaffRememberToken;
use Faker\Generator;

/**
 * StaffRememberToken Examples.
 *
 * @property-read StaffRememberToken[] $staffRememberTokens
 * @mixin \Tests\Unit\Examples\StaffExample
 */
trait StaffRememberTokenExample
{
    /**
     * スタッフのログイン時のリメンバートークン一覧を生成する.
     *
     * @return \Domain\Staff\StaffRememberToken[]
     */
    protected function staffRememberTokens(): array
    {
        return [
            $this->generateStaffRememberTokens(['id' => 1]),
            $this->generateStaffRememberTokens(['id' => 2]),
            $this->generateStaffRememberTokens(['id' => 3]),
            $this->generateStaffRememberTokens(['id' => 4]),
        ];
    }

    /**
     * Generate an example of StaffRememberToken.
     *
     * @param array $overwrites
     * @return \Domain\Staff\StaffRememberToken
     */
    private function generateStaffRememberTokens(array $overwrites)
    {
        $faker = app(Generator::class);
        $values = [
            'staffId' => $this->staffs[0]->id,
            'token' => $faker->text(60),
            'expiredAt' => Carbon::instance($faker->dateTime),
            'createdAt' => Carbon::instance($faker->dateTime),
        ];
        return StaffRememberToken::create($overwrites + $values);
    }
}
