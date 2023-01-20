<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Examples;

use Domain\Common\Carbon;
use Domain\Common\CarbonRange;
use Domain\Common\DefrayerCategory;
use Domain\User\UserLtcsSubsidy;
use Faker\Generator;

/**
 * UserLtcsSubsidy Examples.
 *
 * @property-read \Domain\User\UserLtcsSubsidy[] $userLtcsSubsidies
 * @mixin \Tests\Unit\Examples\UserExample
 */
trait UserLtcsSubsidyExample
{
    /**
     * 公費情報の一覧を生成する.
     *
     * @return \Domain\User\UserLtcsSubsidy[]
     */
    protected function userLtcsSubsidies(): array
    {
        return [
            $this->generateUserLtcsSubsidy([
                'id' => 1,
                'userId' => $this->users[0]->id,
                'period' => CarbonRange::create([
                    'start' => Carbon::create(2020, 1, 1),
                    'end' => Carbon::create(2020, 12, 31),
                ]),
            ]),
            $this->generateUserLtcsSubsidy([
                'id' => 2,
                'userId' => $this->users[1]->id,
                'period' => CarbonRange::create([
                    'start' => Carbon::create(2020, 1, 1),
                    'end' => Carbon::create(2020, 6, 30),
                ]),
            ]),
            $this->generateUserLtcsSubsidy([
                'id' => 3,
                'userId' => $this->users[2]->id,
                'period' => CarbonRange::create([
                    'start' => Carbon::create(2020, 7, 1),
                    'end' => Carbon::create(2020, 12, 31),
                ]),
            ]),
            $this->generateUserLtcsSubsidy([
                'id' => 4,
                'userId' => $this->users[3]->id,
                'period' => CarbonRange::create([
                    'start' => Carbon::create(2010, 1, 1),
                    'end' => Carbon::create(2030, 12, 31),
                ]),
            ]),
        ];
    }

    /**
     * Generate an example of Subsidy.
     *
     * @param array $overwrites
     * @return UserLtcsSubsidy
     */
    protected function generateUserLtcsSubsidy(array $overwrites): UserLtcsSubsidy
    {
        /** @var \Faker\Generator $faker */
        $faker = app(Generator::class);
        $start = Carbon::instance($faker->dateTime)->startOfDay();
        $attrs = [
            'userId' => $this->users[0]->id,
            'period' => CarbonRange::create([
                'start' => $start,
                'end' => $start->addDays($faker->randomDigitNotNull),
            ]),
            'defrayerCategory' => $faker->randomElement(DefrayerCategory::all()),
            'defrayerNumber' => $faker->numerify('########'),
            'recipientNumber' => $faker->numerify('#######'),
            'benefitRate' => 100,
            'copay' => 0,
            'isEnabled' => $faker->boolean,
            'version' => 1,
            'updatedAt' => Carbon::instance($faker->dateTime),
            'createdAt' => Carbon::instance($faker->dateTime),
        ];
        return UserLtcsSubsidy::create($overwrites + $attrs);
    }
}
