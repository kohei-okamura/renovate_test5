<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Examples;

use Domain\Common\Carbon;
use Domain\Project\LtcsProjectServiceCategory;
use Domain\Project\LtcsProjectServiceMenu;
use Faker\Generator;

/**
 * LtcsProjectServiceMenu Example.
 *
 * @property-read LtcsProjectServiceMenu[] $ltcsProjectServiceMenus
 */
trait LtcsProjectServiceMenuExample
{
    /**
     * 介護保険サービス：計画：サービス内容の一覧を生成する.
     *
     * @return \Domain\Project\LtcsProjectServiceMenu[]
     */
    protected function ltcsProjectServiceMenus(): array
    {
        $faker = app(Generator::class);
        return [
            $this->generateLtcsProjectServiceMenu(['id' => 1, 'sortOrder' => 1], $faker),
            $this->generateLtcsProjectServiceMenu(['id' => 2, 'sortOrder' => 2], $faker),
            $this->generateLtcsProjectServiceMenu(['id' => 3, 'sortOrder' => 3], $faker),
            $this->generateLtcsProjectServiceMenu(['id' => 4, 'sortOrder' => 4], $faker),
            $this->generateLtcsProjectServiceMenu(['id' => 5, 'sortOrder' => 5], $faker),
            $this->generateLtcsProjectServiceMenu(['id' => 6, 'sortOrder' => 6], $faker),
            $this->generateLtcsProjectServiceMenu(['id' => 7, 'sortOrder' => 7], $faker),
            $this->generateLtcsProjectServiceMenu(['id' => 8, 'sortOrder' => 8], $faker),
            $this->generateLtcsProjectServiceMenu(['id' => 9, 'sortOrder' => 9], $faker),
            $this->generateLtcsProjectServiceMenu(['id' => 10, 'sortOrder' => 10], $faker),
            $this->generateLtcsProjectServiceMenu(['id' => 11, 'sortOrder' => 11], $faker),
            $this->generateLtcsProjectServiceMenu(['id' => 12, 'sortOrder' => 12], $faker),
            $this->generateLtcsProjectServiceMenu(['id' => 13, 'sortOrder' => 13], $faker),
            $this->generateLtcsProjectServiceMenu(['id' => 14, 'sortOrder' => 14], $faker),
            $this->generateLtcsProjectServiceMenu(['id' => 15, 'sortOrder' => 15], $faker),
        ];
    }

    /**
     * Generate an example of LtcsProjectServiceMenu.
     *
     * @param array $overwrites
     * @param \Faker\Generator $faker
     * @return \Domain\Project\LtcsProjectServiceMenu
     */
    protected function generateLtcsProjectServiceMenu(array $overwrites, Generator $faker): LtcsProjectServiceMenu
    {
        $faker = app(Generator::class);
        $attrs = [
            'category' => $faker->randomElement(LtcsProjectServiceCategory::all()),
            'name' => $faker->text(10),
            'displayName' => $faker->text(10),
            'createdAt' => Carbon::instance($faker->dateTime),
        ];
        return LtcsProjectServiceMenu::create($overwrites + $attrs);
    }
}
