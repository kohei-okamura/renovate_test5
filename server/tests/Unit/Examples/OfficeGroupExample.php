<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Examples;

use Domain\Common\Carbon;
use Domain\Office\OfficeGroup;
use Faker\Generator;

/**
 * Office Group Example.
 *
 * @property-read OfficeGroup[] $officeGroups
 * @mixin \Tests\Unit\Examples\OrganizationExample
 */
trait OfficeGroupExample
{
    /**
     * 事業所グループを生成する.
     *
     * @param array $overwrites
     * @return \Domain\Office\OfficeGroup
     */
    public function generateOfficeGroup(array $overwrites): OfficeGroup
    {
        $faker = app(Generator::class);
        $attrs = [
            'parentOfficeGroupId' => null,
            'name' => $faker->text(100),
            'sortOrder' => $faker->unique()->numberBetween(1),
            'createdAt' => Carbon::instance($faker->dateTime),
            'updatedAt' => Carbon::instance($faker->dateTime),
        ];
        return OfficeGroup::create($overwrites + $attrs);
    }

    /**
     * 事業所グループの一覧を生成する.
     *
     * @return \Domain\Office\OfficeGroup[]
     */
    protected function officeGroups(): array
    {
        return [
            $this->generateOfficeGroup([
                'id' => 1,
                'organizationId' => $this->organizations[0]->id,
                'name' => '事業所グループテスト',
            ]),
            $this->generateOfficeGroup([
                'id' => 2,
                'organizationId' => $this->organizations[0]->id,
            ]),
            $this->generateOfficeGroup([
                'id' => 3,
                'organizationId' => $this->organizations[0]->id,
                'parentOfficeGroupId' => 1,
            ]),
            $this->generateOfficeGroup([
                'id' => 4,
                'organizationId' => $this->organizations[1]->id,
            ]),
            $this->generateOfficeGroup([
                'id' => 5,
                'organizationId' => $this->organizations[0]->id,
            ]),
            $this->generateOfficeGroup([
                'id' => 6,
                'organizationId' => $this->organizations[0]->id,
            ]),
            $this->generateOfficeGroup([
                'id' => 7,
                'organizationId' => $this->organizations[0]->id,
                'parentOfficeGroupId' => 1,
            ]),
            $this->generateOfficeGroup([
                'id' => 8,
                'organizationId' => $this->organizations[1]->id,
            ]),
            $this->generateOfficeGroup([
                'id' => 9,
                'organizationId' => $this->organizations[0]->id,
            ]),
            $this->generateOfficeGroup([
                'id' => 10,
                'organizationId' => $this->organizations[0]->id,
            ]),
            $this->generateOfficeGroup([
                'id' => 11,
                'organizationId' => $this->organizations[0]->id,
                'parentOfficeGroupId' => 1,
            ]),
            $this->generateOfficeGroup([
                'id' => 12,
                'organizationId' => $this->organizations[1]->id,
            ]),
            $this->generateOfficeGroup([
                'id' => 13,
                'organizationId' => $this->organizations[0]->id,
            ]),
            $this->generateOfficeGroup([
                'id' => 14,
                'organizationId' => $this->organizations[0]->id,
            ]),
            $this->generateOfficeGroup([
                'id' => 15,
                'organizationId' => $this->organizations[0]->id,
                'parentOfficeGroupId' => 1,
            ]),
            $this->generateOfficeGroup([
                'id' => 16,
                'organizationId' => $this->organizations[1]->id,
            ]),
            $this->generateOfficeGroup([
                'id' => 17,
                'organizationId' => $this->organizations[0]->id,
            ]),
            $this->generateOfficeGroup([
                'id' => 18,
                'organizationId' => $this->organizations[0]->id,
            ]),
            $this->generateOfficeGroup([
                'id' => 19,
                'organizationId' => $this->organizations[0]->id,
                'parentOfficeGroupId' => 1,
            ]),
            $this->generateOfficeGroup([
                'id' => 20,
                'organizationId' => $this->organizations[1]->id,
            ]),
        ];
    }
}
