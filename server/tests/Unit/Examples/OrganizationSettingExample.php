<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Examples;

use Domain\Common\Carbon;
use Domain\Organization\OrganizationSetting;
use Faker\Generator;

/**
 * OwnExpenseProgram Example.
 *
 * @property-read \Domain\Organization\OrganizationSetting[] $organizationSettings
 * @mixin \Tests\Unit\Examples\OrganizationExample
 */
trait OrganizationSettingExample
{
    /**
     * 事業者別設定の一覧を生成する.
     *
     * @return \Domain\Organization\OrganizationSetting[]
     */
    protected function organizationSettings(): array
    {
        $faker = app(Generator::class);
        return [
            $this->generateOrganizationSetting([
                'id' => 1,
                'organizationId' => $this->organizations[0]->id,
                'bankingClientCode' => '0123456789',
                'bankingClientName' => 'ﾕｰｽﾀｲﾙﾗﾎﾞﾗﾄﾘｰ',
            ], $faker),
            $this->generateOrganizationSetting([
                'id' => 2,
                'organizationId' => $this->organizations[1]->id,
                'bankingClientCode' => '1234567890',
                'bankingClientName' => 'ｲﾀｸｼｬﾒｲ',
            ], $faker),
        ];
    }

    /**
     * Generate an example of OrganizationSetting.
     *
     * @param array $overwrites
     * @param \Faker\Generator $faker
     * @return \Domain\Organization\OrganizationSetting
     */
    protected function generateOrganizationSetting(array $overwrites, Generator $faker): OrganizationSetting
    {
        $attrs = [
            'organizationId' => $this->organizations[0]->id,
            'bankingClientCode' => $faker->numerify(str_repeat('#', 10)),
            'updatedAt' => Carbon::instance($faker->dateTime),
            'createdAt' => Carbon::instance($faker->dateTime),
        ];
        return OrganizationSetting::create($overwrites + $attrs);
    }
}
