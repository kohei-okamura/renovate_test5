<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

use Domain\Common\Carbon;
use Infrastructure\Permission\Permission;
use Infrastructure\Permission\PermissionGroup;

/** @var \Illuminate\Database\Eloquent\Factory $factory */
assert(isset($factory));

$factory->define(Permission::class, function (Faker\Generator $faker) {
    return [
        'id' => $faker->unique()->randomNumber(9, true),
        'permission_group_id' => function () {
            return factory(PermissionGroup::class)->create()->id;
        },
        'code' => $faker->unique()->text(100),
        'name' => $faker->text(100),
        'display_name' => $faker->text(100),
        'sort_order' => $faker->unique()->randomNumber(),
        'created_at' => Carbon::instance($faker->dateTime),
    ];
});
