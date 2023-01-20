<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Examples;

use Domain\Common\Addr;
use Domain\Common\Carbon;
use Domain\Common\Prefecture;
use Domain\Organization\Organization;
use Faker\Generator;

/**
 * Organization Example.
 *
 * @property-read Organization[] $organizations
 */
trait OrganizationExample
{
    /**
     * 事業者を生成する.
     *
     * @param array $overwrites
     * @return \Domain\Organization\Organization
     */
    public function generateOrganization(array $overwrites): Organization
    {
        $faker = app(Generator::class);
        $attrs = [
            'code' => $faker->unique()->text(10),
            'name' => $faker->company,
            'addr' => new Addr(
                postcode: $faker->postcode,
                prefecture: $faker->randomElement(Prefecture::all()),
                city: $faker->city,
                street: $faker->streetAddress,
                apartment: $faker->streetSuffix,
            ),
            'tel' => '01-2345-6789',
            'fax' => '01-2000-6789',
            'isEnabled' => $faker->boolean,
            'version' => 1,
            'createdAt' => Carbon::instance($faker->dateTime),
            'updatedAt' => Carbon::instance($faker->dateTime),
        ];
        return Organization::create($overwrites + $attrs);
    }

    /**
     * 事業者の一覧を生成する.
     *
     * @return \Domain\Organization\Organization[]
     */
    protected function organizations(): array
    {
        return [
            $this->generateOrganization(['id' => 1, 'code' => 'eustylelab1', 'isEnabled' => true]),
            $this->generateOrganization(['id' => 2]),
            $this->generateOrganization(['id' => 3]),
            $this->generateOrganization(['id' => 4]),
            $this->generateOrganization(['id' => 5]),
            $this->generateOrganization(['id' => 6]),
        ];
    }
}
