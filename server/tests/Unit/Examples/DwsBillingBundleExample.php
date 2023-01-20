<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Examples;

use Domain\Billing\DwsBillingBundle;
use Domain\Billing\DwsBillingServiceDetail;
use Domain\Common\Carbon;
use Domain\ServiceCode\ServiceCode;
use Domain\ServiceCodeDictionary\DwsServiceCodeCategory;
use Faker\Generator;

/**
 * DwsBillingBundle Examples.
 *
 * @property-read \Domain\Billing\DwsBillingBundle[] $dwsBillingBundles
 * @mixin \Tests\Unit\Examples\AttendanceExample
 * @mixin \Tests\Unit\Examples\UserExample
 */
trait DwsBillingBundleExample
{
    /**
     * 障害福祉サービス請求単位の一覧を生成する.
     *
     * @return \Domain\Billing\DwsBillingBundle[]
     */
    protected function dwsBillingBundles(): array
    {
        $faker = app(Generator::class);
        assert($faker instanceof Generator);
        return [
            $this->generateDwsBillingBundle([
                'id' => 1,
                'dwsBillingId' => 1,
            ]),
            $this->generateDwsBillingBundle([
                'id' => 2,
                'dwsBillingId' => 1,
            ]),
            $this->generateDwsBillingBundle([
                'id' => 3,
                'dwsBillingId' => 2,
            ]),
            $this->generateDwsBillingBundle([
                'id' => 4,
                'dwsBillingId' => 2,
            ]),
            $this->generateDwsBillingBundle([
                'id' => 5,
                'dwsBillingId' => 2,
                'details' => [
                    DwsBillingServiceDetail::create([
                        'userId' => $this->users[0]->id,
                        'providedOn' => Carbon::today(),
                        'serviceCode' => ServiceCode::fromString('111111'),
                        'serviceCodeCategory' => DwsServiceCodeCategory::physicalCare(),
                        'unitScore' => $faker->numberBetween(0, 2000),
                        'isAddition' => false,
                        'count' => 1,
                        'totalScore' => $faker->numberBetween(0, 2000),
                    ]),
                    DwsBillingServiceDetail::create([
                        'userId' => $this->users[0]->id,
                        'providedOn' => Carbon::today(),
                        'serviceCode' => ServiceCode::fromString('111112'),
                        'serviceCodeCategory' => DwsServiceCodeCategory::physicalCare(),
                        'unitScore' => $faker->numberBetween(0, 2000),
                        'isAddition' => false,
                        'count' => 1,
                        'totalScore' => $faker->numberBetween(0, 2000),
                    ]),
                ],
            ]),
            $this->generateDwsBillingBundle([
                'id' => 6,
                'dwsBillingId' => 1,
                'details' => [
                    DwsBillingServiceDetail::create([
                        'resultId' => $this->attendances[5]->id,
                        'userId' => $this->users[0]->id,
                        'providedOn' => Carbon::today()->subDays(3),
                        'serviceCode' => ServiceCode::fromString('111111'),
                        'serviceCodeCategory' => DwsServiceCodeCategory::physicalCare(),
                        'unitScore' => 319,
                        'isAddition' => false,
                        'count' => 1,
                        'totalScore' => 319,
                    ]),
                    DwsBillingServiceDetail::create([
                        'resultId' => $this->attendances[5]->id,
                        'userId' => $this->users[0]->id,
                        'providedOn' => Carbon::today()->subDays(2),
                        'serviceCode' => ServiceCode::fromString('111112'),
                        'serviceCodeCategory' => DwsServiceCodeCategory::physicalCare(),
                        'unitScore' => 219,
                        'isAddition' => false,
                        'count' => 1,
                        'totalScore' => 219,
                    ]),
                    DwsBillingServiceDetail::create([
                        'resultId' => $this->attendances[5]->id,
                        'userId' => $this->users[0]->id,
                        'providedOn' => Carbon::today()->subDay(),
                        'serviceCode' => ServiceCode::fromString('111113'),
                        'serviceCodeCategory' => DwsServiceCodeCategory::physicalCare(),
                        'isAddition' => true,
                        'unitScore' => 100,
                        'count' => 2,
                        'totalScore' => 200,
                    ]),
                ],
            ]),
            $this->generateDwsBillingBundle([
                'id' => 7,
                'dwsBillingId' => 1,
                'details' => [
                    DwsBillingServiceDetail::create([
                        'resultId' => $this->attendances[5]->id,
                        'userId' => $this->users[0]->id,
                        'providedOn' => Carbon::today()->subDays(3),
                        'serviceCode' => ServiceCode::fromString('111111'),
                        'serviceCodeCategory' => DwsServiceCodeCategory::physicalCare(),
                        'unitScore' => 319,
                        'isAddition' => false,
                        'count' => 1,
                        'totalScore' => 319,
                    ]),
                    DwsBillingServiceDetail::create([
                        'resultId' => $this->attendances[5]->id,
                        'userId' => $this->users[1]->id,
                        'providedOn' => Carbon::today()->subDays(2),
                        'serviceCode' => ServiceCode::fromString('111112'),
                        'serviceCodeCategory' => DwsServiceCodeCategory::physicalCare(),
                        'unitScore' => 219,
                        'isAddition' => false,
                        'count' => 1,
                        'totalScore' => 219,
                    ]),
                    DwsBillingServiceDetail::create([
                        'resultId' => $this->attendances[5]->id,
                        'userId' => $this->users[2]->id,
                        'providedOn' => Carbon::today()->subDay(),
                        'serviceCode' => ServiceCode::fromString('111113'),
                        'serviceCodeCategory' => DwsServiceCodeCategory::physicalCare(),
                        'isAddition' => true,
                        'unitScore' => 100,
                        'count' => 2,
                        'totalScore' => 200,
                    ]),
                ],
            ]),
            $this->generateDwsBillingBundle([
                'id' => 8,
                'dwsBillingId' => 1,
                'providedIn' => Carbon::parse('2020-11-10'),
            ]),
            $this->generateDwsBillingBundle([
                'id' => 9,
                'dwsBillingId' => 1,
                'providedIn' => Carbon::parse('2020-11-10'),
            ]),
            // 明細書状態一括更新 API、サービス提供実績記録状態一括更新 API で使っている
            $this->generateDwsBillingBundle([
                'id' => 10,
                'dwsBillingId' => 8,
                'providedIn' => Carbon::parse('2020-11-10'),
            ]),
            // 請求額 0 円で使っている
            $this->generateDwsBillingBundle([
                'id' => 11,
                'dwsBillingId' => $this->dwsBillings[8]->id,
                'providedIn' => Carbon::parse('2020-11-10'),
            ]),
            // 利用者負担額一覧表ダウンロード用
            $this->generateDwsBillingBundle([
                'id' => 12,
                'dwsBillingId' => $this->dwsBillings[9]->id,
                'providedIn' => Carbon::parse('2021-11-10'),
            ]),
        ];
    }

    /**
     * Generate an example of DwsBillingBundle.
     *
     * @param array $overwrites
     * @return \Domain\Billing\DwsBillingBundle
     */
    private function generateDwsBillingBundle(array $overwrites): DwsBillingBundle
    {
        $faker = app(Generator::class);
        assert($faker instanceof Generator);
        $faker->seed(1);
        $score = $faker->numberBetween(0, 2000);
        $values = [
            'providedIn' => Carbon::parse('2020-10-10'),
            'cityCode' => '123456',
            'cityName' => $faker->city,
            'details' => [
                DwsBillingServiceDetail::create([
                    'userId' => $this->users[0]->id,
                    'providedOn' => Carbon::today(),
                    'serviceCode' => ServiceCode::fromString('111111'),
                    'serviceCodeCategory' => DwsServiceCodeCategory::physicalCare(),
                    'unitScore' => $score,
                    'isAddition' => false,
                    'count' => 1,
                    'totalScore' => $score,
                ]),
            ],
            'createdAt' => Carbon::instance($faker->dateTime),
            'updatedAt' => Carbon::instance($faker->dateTime),
        ];
        return DwsBillingBundle::create($overwrites + $values);
    }
}
