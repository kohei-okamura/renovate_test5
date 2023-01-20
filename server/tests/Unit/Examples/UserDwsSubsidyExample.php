<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Examples;

use Domain\Common\Carbon;
use Domain\Common\CarbonRange;
use Domain\Common\Rounding;
use Domain\User\UserDwsSubsidy;
use Domain\User\UserDwsSubsidyFactor;
use Domain\User\UserDwsSubsidyType;
use Faker\Generator;

/**
 * UserDwsSubsidy Example.
 *
 * @property-read \Domain\User\UserDwsSubsidy[] $userDwsSubsidies
 * @mixin \Tests\Unit\Examples\UserExample
 */
trait UserDwsSubsidyExample
{
    /**
     * 自治体助成情報の一覧を生成する.
     *
     * @return array
     */
    protected function userDwsSubsidies(): array
    {
        $faker = app(Generator::class);
        return [
            $this->generateUserDwsSubsidy($faker, [
                'id' => 1,
                'period' => CarbonRange::create([
                    'start' => Carbon::create(2020, 1, 1),
                    'end' => Carbon::create(2020, 12, 31),
                ]),
            ]),
            $this->generateUserDwsSubsidy($faker, [
                'id' => 2,
                'period' => CarbonRange::create([
                    'start' => Carbon::create(2020, 1, 1),
                    'end' => Carbon::create(2020, 6, 30),
                ]),
            ]),
            $this->generateUserDwsSubsidy($faker, [
                'id' => 3,
                'userId' => $this->users[1]->id,
                'period' => CarbonRange::create([
                    'start' => Carbon::create(2020, 7, 1),
                    'end' => Carbon::create(2020, 12, 31),
                ]),
            ]),
        ];
    }

    /**
     * 自治体助成情報を生成する.
     *
     * @param \Faker\Generator $faker
     * @param array $overwrites
     * @return \Domain\User\UserDwsSubsidy
     */
    private function generateUserDwsSubsidy(Generator $faker, array $overwrites): UserDwsSubsidy
    {
        $start = Carbon::instance($faker->dateTime)->startOfDay();
        $attrs = [
            'userId' => $this->users[0]->id,
            'period' => CarbonRange::create([
                'start' => $start,
                'end' => $start->addDays($faker->randomDigitNotNull),
            ]),
            'cityName' => $faker->city,
            'cityCode' => $faker->numerify('######'),
            'subsidyType' => $faker->randomElement(UserDwsSubsidyType::all()),
            'factor' => UserDwsSubsidyFactor::copay(),
            'benefitRate' => 100,
            'copayRate' => 0,
            'rounding' => Rounding::floor(),
            'benefitAmount' => 0,
            'copayAmount' => 0,
            'note' => '',
            'createdAt' => Carbon::parse($faker->dateTime),
            'updatedAt' => Carbon::parse($faker->dateTime),
        ];
        return UserDwsSubsidy::create($overwrites + $attrs);
    }
}
