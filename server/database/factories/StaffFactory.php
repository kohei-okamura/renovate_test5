<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

use Domain\Common\Addr;
use Domain\Common\Carbon;
use Domain\Common\Location;
use Domain\Common\Password;
use Domain\Common\Prefecture;
use Domain\Common\Sex;
use Domain\Common\StructuredName;
use Infrastructure\Organization\Organization;
use Infrastructure\Organization\OrganizationAttr;
use Infrastructure\Staff\Staff;
use Infrastructure\Staff\StaffAttr;
use Infrastructure\Staff\StaffEmailVerification;
use Infrastructure\Staff\StaffPasswordReset;
use Infrastructure\Staff\StaffRememberToken;

/** @var \Illuminate\Database\Eloquent\Factory $factory */
assert(isset($factory));

$factory->define(Staff::class, function (Faker\Generator $faker) {
    return [
        'id' => $faker->unique()->randomNumber(9, true),
        'organization_id' => function () {
            $organization = factory(Organization::class)->create();
            $organization->attr()->save(factory(OrganizationAttr::class)->make());
            return $organization->id;
        },
        'created_at' => Carbon::instance($faker->dateTime),
    ];
});

$factory->define(StaffAttr::class, function (Faker\Generator $faker) {
    return [
        'id' => $faker->unique()->randomNumber(9, true),
        'employee_number' => $faker->text(20),
        'name' => new StructuredName(
            familyName: $faker->lastName,
            givenName: $faker->firstName,
            phoneticFamilyName: $faker->format('lastKanaName'),
            phoneticGivenName: $faker->format('firstKanaName'),
        ),
        'sex' => $faker->randomElement([Sex::notKnown(), Sex::male(), Sex::female(), Sex::notApplicable()]),
        'birthday' => $faker->date(),
        'addr' => new Addr(
            postcode: $faker->postcode,
            prefecture: $faker->randomElement(Prefecture::all()),
            city: $faker->city,
            street: $faker->streetAddress,
            apartment: $faker->streetSuffix,
        ),
        'location' => Location::create([
            'lat' => $faker->randomFloat(6, -90, 90),
            'lng' => $faker->randomFloat(6, -180, 180),
        ]),
        'tel' => $faker->phoneNumber,
        'email' => $faker->email,
        'password' => Password::fromString($faker->password),
        'is_verified' => $faker->boolean,
        'is_enabled' => $faker->boolean,
        'version' => 1,
        'updated_at' => Carbon::instance($faker->dateTime),
    ];
});

$factory->define(StaffEmailVerification::class, function (Faker\Generator $faker) {
    return [
        'id' => $faker->unique()->randomNumber(9, true),
        'staff_id' => function () {
            $staff = factory(Staff::class)->create();
            $staff->attr()->save(factory(StaffAttr::class)->make());
            return $staff->id;
        },
        'email' => $faker->email,
        'token' => $faker->text(60),
        'expired_at' => Carbon::instance($faker->dateTime),
        'created_at' => Carbon::instance($faker->dateTime),
    ];
});

$factory->define(StaffPasswordReset::class, function (Faker\Generator $faker) {
    return [
        'id' => $faker->unique()->randomNumber(),
        'staff_id' => function () {
            $staff = factory(Staff::class)->create();
            $staff->attr()->save(factory(StaffAttr::class)->make());
            return $staff->id;
        },
        'email' => $faker->email,
        'token' => $faker->text(60),
        'expired_at' => Carbon::instance($faker->dateTime),
        'created_at' => Carbon::instance($faker->dateTime),
    ];
});

$factory->define(StaffRememberToken::class, function (Faker\Generator $faker) {
    return [
        'id' => $faker->unique()->randomNumber(),
        'staff_id' => function () {
            $staff = factory(Staff::class)->create();
            $staff->attr()->save(factory(StaffAttr::class)->make());
            return $staff->id;
        },
        'token' => $faker->text(60),
        'expired_at' => Carbon::instance($faker->dateTime),
        'created_at' => Carbon::instance($faker->dateTime),
    ];
});
