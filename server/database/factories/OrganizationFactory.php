<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

use Domain\Common\Addr;
use Domain\Common\Carbon;
use Domain\Common\Prefecture;
use Infrastructure\Organization\Organization;
use Infrastructure\Organization\OrganizationAttr;

/** @var \Illuminate\Database\Eloquent\Factory $factory */
assert(isset($factory));

$factory->define(Organization::class, function (Faker\Generator $faker) {
    return [
        'id' => $faker->unique()->randomNumber(9, true),
        'code' => $faker->unique()->text(10),
        'created_at' => Carbon::instance($faker->dateTime),
    ];
});

$factory->define(OrganizationAttr::class, function (Faker\Generator $faker) {
    return [
        'id' => $faker->unique()->randomNumber(9, true),
        'name' => $faker->company,
        'addr' => function () use ($faker) {
            return new Addr(
                postcode: $faker->postcode,
                prefecture: $faker->randomElement(Prefecture::all()),
                city: $faker->city,
                street: $faker->streetAddress,
                apartment: $faker->streetSuffix,
            );
        },
        'tel' => $faker->phoneNumber,
        'fax' => $faker->phoneNumber,
        'is_enabled' => true,
        'version' => 1,
        'updated_at' => Carbon::instance($faker->dateTime),
    ];
});
