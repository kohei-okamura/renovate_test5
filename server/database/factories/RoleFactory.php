<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

use Domain\Common\Carbon;
use Infrastructure\Organization\Organization;
use Infrastructure\Organization\OrganizationAttr;
use Infrastructure\Role\Role;

/** @var \Illuminate\Database\Eloquent\Factory $factory */
assert(isset($factory));

$factory->define(Role::class, function (Faker\Generator $faker) {
    return [
        'id' => $faker->unique()->randomNumber(9, true),
        'organization_id' => function () {
            $organization = factory(Organization::class)->create();
            $organization->attr()->save(factory(OrganizationAttr::class)->make());
            return $organization->id;
        },
        'name' => $faker->text(100),
        'is_system_admin' => $faker->boolean,
        'sort_order' => $faker->unique()->randomNumber(),
        'created_at' => Carbon::instance($faker->dateTime),
        'updated_at' => Carbon::instance($faker->dateTime),
    ];
});
