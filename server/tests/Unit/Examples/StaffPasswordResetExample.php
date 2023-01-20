<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Examples;

use Domain\Common\Carbon;
use Domain\Staff\StaffPasswordReset;
use Faker\Generator;

/**
 * StaffPasswordReset Examples.
 *
 * @property-read StaffPasswordReset[] $staffPasswordResets
 * @mixin \Tests\Unit\Examples\StaffExample
 */
trait StaffPasswordResetExample
{
    /**
     * Generate an example of StaffPasswordReset.
     *
     * @param array $overwrites
     * @return \Domain\Staff\StaffPasswordReset
     */
    protected function generateStaffPasswordReset(array $overwrites)
    {
        $faker = app(Generator::class);
        $values = [
            'staffId' => $this->staffs[0]->id,
            'name' => $this->staffs[0]->name,
            'token' => $faker->text(60),
            'email' => $faker->email,
            'expiredAt' => Carbon::instance($faker->dateTime),
            'createdAt' => Carbon::instance($faker->dateTime),
        ];
        return StaffPasswordReset::create($overwrites + $values);
    }

    /**
     * スタッフパスワード再設定の一覧を生成する.
     *
     * @return \Domain\Staff\StaffPasswordReset[]
     */
    protected function staffPasswordResets(): array
    {
        return [
            $this->generateStaffPasswordReset(['id' => 1]),
            $this->generateStaffPasswordReset(['id' => 2]),
            $this->generateStaffPasswordReset(['id' => 3]),
            $this->generateStaffPasswordReset(['id' => 4]),
            $this->generateStaffPasswordReset([
                'id' => 5,
                'expiredAt' => Carbon::now()->addHour(),
            ]),
            $this->generateStaffPasswordReset([
                'id' => 6,
                'expiredAt' => Carbon::now()->subSecond(),
            ]),
        ];
    }
}
