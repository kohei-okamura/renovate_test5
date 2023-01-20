<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Examples;

use Domain\Billing\DwsBilling;
use Domain\Billing\DwsBillingFile;
use Domain\Billing\DwsBillingOffice;
use Domain\Billing\DwsBillingStatus;
use Domain\Common\Carbon;
use Domain\Common\MimeType;
use Faker\Generator;

/**
 * DwsBilling Examples.
 *
 * @property-read \Domain\Billing\DwsBilling[] $dwsBillings
 * @mixin \Tests\Unit\Examples\OfficeExample
 * @mixin \Tests\Unit\Examples\DwsBillingBundleExample
 */
trait DwsBillingExample
{
    /**
     * 障害福祉サービス請求の一覧を生成する.
     *
     * @return \Domain\Billing\DwsBilling[]
     */
    protected function dwsBillings(): array
    {
        $faker = app(Generator::class);
        assert($faker instanceof Generator);
        $faker->seed(1);
        return [
            $this->generateDwsBilling([
                'id' => 1,
                'organizationId' => 1,
                'status' => DwsBillingStatus::ready(),
                'transactedIn' => Carbon::create(2021, 4),
            ], $faker),
            $this->generateDwsBilling([
                'id' => 2,
                'organizationId' => 1,
                'office' => DwsBillingOffice::from($this->offices[1]),
            ], $faker),
            $this->generateDwsBilling([
                'id' => 3,
                'organizationId' => 1,
                'files' => [], // removeByIdのテスト用
            ], $faker),
            $this->generateDwsBilling([
                'id' => 4,
                'organizationId' => 2,
                'files' => [], // removeByIdのテスト用
            ], $faker),
            $this->generateDwsBilling([
                'id' => 5,
                'organizationId' => 2,
                'transactedIn' => Carbon::parse('2021-01-01'),
            ], $faker),
            $this->generateDwsBilling([
                'id' => 6,
                'organizationId' => 1,
                'transactedIn' => Carbon::parse('2021-03-01'),
            ], $faker),
            $this->generateDwsBilling([
                'id' => 7,
                'organizationId' => 1,
                'transactedIn' => Carbon::parse('2020-11-01'),
                'status' => DwsBillingStatus::fixed(),
            ], $faker),
            // 明細書状態一括更新 API で使っている
            $this->generateDwsBilling([
                'id' => 8,
                'organizationId' => 1,
                'transactedIn' => Carbon::parse('2020-12-01'),
                'status' => DwsBillingStatus::ready(),
            ], $faker),
            //
            // 請求額 0 円で使っている
            $this->generateDwsBilling([
                'id' => 9,
                'organizationId' => 1,
                'status' => DwsBillingStatus::ready(),
            ], $faker),
            // 利用者負担額一覧表ダウンロード用
            $this->generateDwsBilling([
                'id' => 10,
                'organizationId' => 1,
                'status' => DwsBillingStatus::ready(),
                'transactedIn' => Carbon::create(2022, 4),
            ], $faker),
        ];
    }

    /**
     * Generate an example of DwsBilling.
     *
     * @param array $overwrites
     * @param \Faker\Generator $faker
     * @return \Domain\Billing\DwsBilling
     */
    private function generateDwsBilling(array $overwrites, Generator $faker): DwsBilling
    {
        $values = [
            'office' => DwsBillingOffice::from($this->offices[0]),
            'transactedIn' => Carbon::today()->startOfMonth(),
            'files' => [
                new DwsBillingFile(
                    name: 'filename.pdf',
                    path: 'dummy/path',
                    token: $faker->text(60),
                    mimeType: $faker->randomElement(MimeType::all()),
                    createdAt: Carbon::instance($faker->dateTime),
                    downloadedAt: Carbon::instance($faker->dateTime),
                ),
            ],
            'status' => $faker->randomElement(DwsBillingStatus::all()),
            'fixedAt' => Carbon::instance($faker->dateTime),
            'createdAt' => Carbon::instance($faker->dateTime),
            'updatedAt' => Carbon::instance($faker->dateTime),
        ];
        return DwsBilling::create($overwrites + $values);
    }
}
