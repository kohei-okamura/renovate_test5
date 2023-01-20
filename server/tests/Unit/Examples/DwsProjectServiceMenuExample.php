<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Examples;

use Domain\Common\Carbon;
use Domain\Project\DwsProjectServiceCategory;
use Domain\Project\DwsProjectServiceMenu;
use Faker\Generator;

/**
 * DwsProjectServiceMenu Example.
 *
 * @property-read DwsProjectServiceMenu[] $dwsProjectServiceMenus
 */
trait DwsProjectServiceMenuExample
{
    /**
     * 障害福祉サービス：計画：サービス内容の一覧を生成する.
     *
     * @return \Domain\Project\DwsProjectServiceMenu[]
     */
    protected function dwsProjectServiceMenus(): array
    {
        $faker = app(Generator::class);
        return [
            $this->generateDwsProjectServiceMenu(['id' => 1, 'sortOrder' => 1], $faker),
            $this->generateDwsProjectServiceMenu(['id' => 2, 'sortOrder' => 2], $faker),
            $this->generateDwsProjectServiceMenu(['id' => 3, 'sortOrder' => 3], $faker),
            $this->generateDwsProjectServiceMenu(['id' => 4, 'sortOrder' => 4], $faker),
            $this->generateDwsProjectServiceMenu(['id' => 5, 'sortOrder' => 5], $faker),
            $this->generateDwsProjectServiceMenu(['id' => 6, 'sortOrder' => 6], $faker),
            $this->generateDwsProjectServiceMenu(['id' => 7, 'sortOrder' => 7], $faker),
            $this->generateDwsProjectServiceMenu(['id' => 8, 'sortOrder' => 8], $faker),
            $this->generateDwsProjectServiceMenu(['id' => 9, 'sortOrder' => 9], $faker),
            $this->generateDwsProjectServiceMenu(['id' => 10, 'sortOrder' => 10], $faker),
            $this->generateDwsProjectServiceMenu(['id' => 11, 'sortOrder' => 11], $faker),
            $this->generateDwsProjectServiceMenu(['id' => 12, 'sortOrder' => 12], $faker),
            $this->generateDwsProjectServiceMenu(['id' => 13, 'sortOrder' => 13], $faker),
            $this->generateDwsProjectServiceMenu(['id' => 14, 'sortOrder' => 14], $faker),
            $this->generateDwsProjectServiceMenu(['id' => 15, 'sortOrder' => 15], $faker),
        ];
    }

    /**
     * Generate an example of DwsProjectServiceMenu.
     *
     * @param array $overwrites
     * @param \Faker\Generator $faker
     * @return \Domain\Project\DwsProjectServiceMenu
     */
    protected function generateDwsProjectServiceMenu(array $overwrites, Generator $faker): DwsProjectServiceMenu
    {
        $faker = app(Generator::class);
        $attrs = [
            'category' => $faker->randomElement(DwsProjectServiceCategory::all()),
            'name' => $faker->text(10),
            'displayName' => $faker->text(10),
            'createdAt' => Carbon::instance($faker->dateTime),
        ];
        return DwsProjectServiceMenu::create($overwrites + $attrs);
    }
}
