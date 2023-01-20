<?php
/**
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Examples;

use Domain\Common\Carbon;
use Domain\User\LtcsUserLocationAddition;
use Domain\User\UserLtcsCalcSpec;
use Faker\Generator;

/**
 * UserLtcsCalcSpec Example.
 *
 * @property-read UserLtcsCalcSpec[] $userLtcsCalcSpecs
 * @mixin \Tests\Unit\Examples\UserExample
 */
trait UserLtcsCalcSpecExample
{
    /**
     * 介護保険サービス：利用者別算定情報の一覧を生成する.
     *
     * @return \Domain\User\UserLtcsCalcSpec[]
     */
    protected function userLtcsCalcSpecs(): array
    {
        $faker = app(Generator::class);
        return [
            $this->generateUserLtcsCalcSpec([
                'id' => 1,
                'effectivatedOn' => Carbon::parse('2021-01-02'),
            ], $faker),
            $this->generateUserLtcsCalcSpec([
                'id' => 2,
                'locationAddition' => LtcsUserLocationAddition::mountainousArea(),
                'effectivatedOn' => Carbon::create(2021),
            ], $faker),
            $this->generateUserLtcsCalcSpec([
                'id' => 3,
                'userId' => $this->users[1]->id,
            ], $faker),
            $this->generateUserLtcsCalcSpec([
                'id' => 4,
                'userId' => $this->users[14]->id,
            ], $faker),
            $this->generateUserLtcsCalcSpec([
                'id' => 5,
                'userId' => $this->users[19]->id,
                'locationAddition' => LtcsUserLocationAddition::mountainousArea(),
                'effectivatedOn' => Carbon::create(2021, 1, 1),
            ], $faker),
        ];
    }

    /**
     * Generate an example of UserLtcsCalcSpec.
     *
     * @param array $overwrites
     * @param \Faker\Generator $faker
     * @return \Domain\User\UserLtcsCalcSpec
     */
    protected function generateUserLtcsCalcSpec(array $overwrites, Generator $faker): UserLtcsCalcSpec
    {
        $attrs = [
            'userId' => $this->users[0]->id,
            'effectivatedOn' => Carbon::create(2020),
            'locationAddition' => LtcsUserLocationAddition::none(),
            'isEnabled' => true,
            'version' => 1,
            'updatedAt' => Carbon::instance($faker->dateTime),
            'createdAt' => Carbon::instance($faker->dateTime),
        ];
        return UserLtcsCalcSpec::fromAssoc($overwrites + $attrs);
    }
}
