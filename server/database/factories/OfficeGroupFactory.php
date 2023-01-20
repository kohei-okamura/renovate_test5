<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

use Domain\Common\Carbon;
use Infrastructure\Office\OfficeGroup;
use Infrastructure\Organization\Organization;
use Infrastructure\Organization\OrganizationAttr;

/** @var \Illuminate\Database\Eloquent\Factory $factory */
assert(isset($factory));

$factory->define(OfficeGroup::class, function (Faker\Generator $faker) {
    return [
        'id' => $faker->unique()->randomNumber(9, true),
        'organization_id' => function (): void {
            $organization = factory(Organization::class)->create();
            $organization->attr()->save(factory(OrganizationAttr::class)->make());
        },
        'parent_office_group_id' => null,
        'name' => $faker->text(100),
        'sort_order' => $faker->unique()->randomNumber(),
        'created_at' => Carbon::instance($faker->dateTime),
        'updated_at' => Carbon::instance($faker->dateTime),
    ];
});
