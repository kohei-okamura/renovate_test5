<?php
/**
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Examples;

use Domain\Common\Carbon;
use Domain\User\DwsUserLocationAddition;
use Domain\User\UserDwsCalcSpec;
use Faker\Generator;

/**
 * UserDwsCalcSpec Example.
 *
 * @property-read UserDwsCalcSpec[] $userDwsCalcSpecs
 * @mixin \Tests\Unit\Examples\UserExample
 */
trait UserDwsCalcSpecExample
{
    /**
     * 障害福祉サービス：利用者別算定情報の一覧を生成する.
     *
     * @return \Domain\User\UserDwsCalcSpec[]
     */
    protected function userDwsCalcSpecs(): array
    {
        $faker = app(Generator::class);
        return [
            $this->generateUserDwsCalcSpec([
                'id' => 1,
                'effectivatedOn' => Carbon::parse('2021-01-02'),
            ], $faker),
            $this->generateUserDwsCalcSpec([
                'id' => 2,
                'locationAddition' => DwsUserLocationAddition::specifiedArea(),
                'effectivatedOn' => Carbon::create(2021),
            ], $faker),
            $this->generateUserDwsCalcSpec([
                'id' => 3,
                'userId' => $this->users[1]->id,
            ], $faker),
            $this->generateUserDwsCalcSpec([
                'id' => 4,
                'userId' => $this->users[14]->id,
            ], $faker),
            $this->generateUserDwsCalcSpec([
                'id' => 5,
                'userId' => $this->users[19]->id,
                'locationAddition' => DwsUserLocationAddition::specifiedArea(),
                'effectivatedOn' => Carbon::create(2021, 1, 1),
            ], $faker),
        ];
    }

    /**
     * Generate an example of UserDwsCalcSpec.
     *
     * @param array $overwrites
     * @param \Faker\Generator $faker
     * @return \Domain\User\UserDwsCalcSpec
     */
    protected function generateUserDwsCalcSpec(array $overwrites, Generator $faker): UserDwsCalcSpec
    {
        $attrs = [
            'userId' => $this->users[0]->id,
            'effectivatedOn' => Carbon::create(2020),
            'locationAddition' => DwsUserLocationAddition::none(),
            'isEnabled' => true,
            'version' => 1,
            'updatedAt' => Carbon::instance($faker->dateTime),
            'createdAt' => Carbon::instance($faker->dateTime),
        ];
        return UserDwsCalcSpec::fromAssoc($overwrites + $attrs);
    }
}
