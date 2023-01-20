<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Examples;

use Domain\Common\Carbon;
use Domain\Staff\StaffEmailVerification;
use Faker\Generator;

/**
 * StaffEmailVerification Examples.
 *
 * @property-read StaffEmailVerification[] $staffEmailVerifications
 * @mixin \Tests\Unit\Examples\StaffExample
 */
trait StaffEmailVerificationExample
{
    /**
     * スタッフメールアドレス確認の一覧を生成する.
     *
     * @return \Domain\Staff\StaffEmailVerification[]
     */
    protected function staffEmailVerifications(): array
    {
        return [
            $this->generateStaffEmailVerification([
                'id' => 1,
                'expiredAt' => Carbon::now()->addMinute()->millisecond(0),
            ]),
            $this->generateStaffEmailVerification([
                'id' => 2,
                'expiredAt' => Carbon::now()->subMinute(),
            ]),
            $this->generateStaffEmailVerification(['id' => 3]),
            $this->generateStaffEmailVerification(['id' => 4]),
            $this->generateStaffEmailVerification([
                'id' => 5,
                'staffId' => $this->staffs[1]->id,
                'name' => $this->staffs[1]->name,
            ]),
        ];
    }

    /**
     * Generate an example of StaffEmailVerification.
     *
     * @param array $overwrites
     * @return \Domain\Staff\StaffEmailVerification
     */
    private function generateStaffEmailVerification(array $overwrites)
    {
        $faker = app(Generator::class);
        $values = [
            'staffId' => $this->staffs[0]->id,
            'name' => $this->staffs[0]->name,
            'token' => $faker->regexify('[A-Za-z0-9]{60}'),
            'email' => $faker->email,
            'expiredAt' => Carbon::instance($faker->dateTime)->millisecond(0),
            'createdAt' => Carbon::instance($faker->dateTime)->millisecond(0),
        ];
        return StaffEmailVerification::create($overwrites + $values);
    }
}
