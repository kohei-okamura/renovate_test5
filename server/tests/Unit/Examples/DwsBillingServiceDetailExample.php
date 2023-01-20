<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Examples;

use Domain\Billing\DwsBillingServiceDetail;
use Domain\Common\Carbon;
use Domain\ServiceCode\ServiceCode;
use Domain\ServiceCodeDictionary\DwsServiceCodeCategory;
use Faker\Generator;

/**
 * DwsBillingServiceDetail Example.
 *
 * @property-read DwsBillingServiceDetail[] $dwsBillingServiceDetails
 * @mixin \Tests\Unit\Examples\UserExample
 */
trait DwsBillingServiceDetailExample
{
    /**
     * 障害福祉サービス：請求：サービス詳細の一覧を生成する.
     *
     * @return \Domain\Billing\DwsBillingServiceDetail[]
     */
    protected function dwsBillingServiceDetails(): array
    {
        return [
            $this->generateDwsBillingServiceDetail([]),
            $this->generateDwsBillingServiceDetail([]),
            $this->generateDwsBillingServiceDetail([]),
        ];
    }

    /**
     * エンティティを生成する.
     *
     * @param array $overwrites
     * @return \Domain\Billing\DwsBillingServiceDetail
     */
    protected function generateDwsBillingServiceDetail(array $overwrites): DwsBillingServiceDetail
    {
        /** @var \Faker\Generator $faker */
        $faker = app(Generator::class);
        $attrs = [
            'userId' => $this->users[0]->id,
            'providedAt' => Carbon::instance($faker->dateTime),
            'serviceCode' => ServiceCode::fromString("{$faker->randomNumber(6, true)}"),
            'serviceCodeCategory' => DwsServiceCodeCategory::physicalCare(),
            'isAddition' => $faker->boolean(),
            'unitScore' => $faker->numberBetween(1, 10),
            'count' => 1,
        ];
        $attrs['totalScore'] = $attrs['unitScore'];
        return DwsBillingServiceDetail::create($overwrites + $attrs);
    }
}
