<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Examples;

use Domain\Billing\LtcsBillingBundle;
use Domain\Billing\LtcsBillingServiceDetail;
use Domain\Billing\LtcsBillingServiceDetailDisposition;
use Domain\Common\Carbon;
use Domain\ProvisionReport\LtcsBuildingSubtraction;
use Domain\ServiceCode\ServiceCode;
use Domain\ServiceCodeDictionary\LtcsNoteRequirement;
use Domain\ServiceCodeDictionary\LtcsServiceCodeCategory;
use Faker\Generator as FakerGenerator;
use Tests\Unit\Faker\Faker;

/**
 * {@link \Domain\Billing\LtcsBillingBundle} Examples.
 *
 * @mixin \Tests\Unit\Examples\LtcsBillingExample
 * @mixin \Tests\Unit\Examples\UserExample
 * @property-read \Domain\Billing\LtcsBillingBundle[] $ltcsBillingBundles
 */
trait LtcsBillingBundleExample
{
    /**
     * 介護保険サービス：請求単位を生成する.
     *
     * @param \Faker\Generator $faker
     * @param array $attrs
     * @return \Domain\Billing\LtcsBillingBundle
     */
    public function generateLtcsBillingBundle(FakerGenerator $faker, array $attrs): LtcsBillingBundle
    {
        $values = [
            'billingId' => $this->ltcsBillings[0]->id,
            'providedIn' => Carbon::parse($faker->date('2021-02-01')),
            'details' => [
                new LtcsBillingServiceDetail(
                    userId: $this->users[0]->id,
                    disposition: LtcsBillingServiceDetailDisposition::plan(),
                    providedOn: Carbon::parse($faker->date('2021-02-01')),
                    serviceCode: ServiceCode::fromString('111812'),
                    serviceCodeCategory: LtcsServiceCodeCategory::physicalCare(),
                    buildingSubtraction: LtcsBuildingSubtraction::none(),
                    noteRequirement: LtcsNoteRequirement::none(),
                    isAddition: false,
                    isLimited: false,
                    durationMinutes: 210,
                    unitScore: 1240,
                    count: 1,
                    wholeScore: 1240,
                    maxBenefitQuotaExcessScore: 0,
                    maxBenefitExcessScore: 0,
                    totalScore: 1240,
                ),
                new LtcsBillingServiceDetail(
                    userId: $this->users[0]->id,
                    disposition: LtcsBillingServiceDetailDisposition::result(),
                    providedOn: Carbon::parse($faker->date('2021-02-01')),
                    serviceCode: ServiceCode::fromString('111812'),
                    serviceCodeCategory: LtcsServiceCodeCategory::physicalCare(),
                    buildingSubtraction: LtcsBuildingSubtraction::none(),
                    noteRequirement: LtcsNoteRequirement::none(),
                    isAddition: false,
                    isLimited: false,
                    durationMinutes: 210,
                    unitScore: 1240,
                    count: 1,
                    wholeScore: 1240,
                    maxBenefitQuotaExcessScore: 0,
                    maxBenefitExcessScore: 0,
                    totalScore: 1240,
                ),
            ],
            'createdAt' => Carbon::instance($faker->dateTime('2021-02-01 00:00:00')),
            'updatedAt' => Carbon::instance($faker->dateTime('2021-02-01 00:00:00')),
        ];
        return LtcsBillingBundle::create($attrs + $values);
    }

    /**
     * 介護保険サービス：請求単位の一覧を生成する.
     *
     * @return array|\Domain\Billing\LtcsBillingBundle[]
     */
    protected function ltcsBillingBundles(): array
    {
        $faker = Faker::make(1943463177);
        return [
            $this->generateLtcsBillingBundle($faker, ['id' => 1]),
            $this->generateLtcsBillingBundle($faker, ['id' => 2]),
            $this->generateLtcsBillingBundle($faker, ['id' => 3]),
            $this->generateLtcsBillingBundle($faker, ['id' => 4]),
            $this->generateLtcsBillingBundle($faker, [
                'id' => 5,
                'billingId' => $this->ltcsBillings[1]->id,
            ]),
            $this->generateLtcsBillingBundle($faker, [
                'id' => 6,
                'billingId' => $this->ltcsBillings[2]->id,
            ]),
            $this->generateLtcsBillingBundle($faker, [
                'id' => 7,
                'billingId' => $this->ltcsBillings[3]->id,
            ]),
            $this->generateLtcsBillingBundle($faker, [
                'id' => 8,
                'providedIn' => Carbon::parse($faker->date('2021-03-01')),
            ]),
            // 請求額 0 円で使っている
            $this->generateLtcsBillingBundle($faker, [
                'id' => 9,
                'billingId' => $this->ltcsBillings[7]->id,
            ]),
            // /ltcs-billings/{id}/status のテスト (Cest) で使っている (id: 10, 11)
            // 介保の場合、1 つの請求にサービス提供年月が同じ請求単位が複数紐づくことはないため注意
            $this->generateLtcsBillingBundle($faker, [
                'id' => 10,
                'billingId' => $this->ltcsBillings[8]->id,
                'providedIn' => Carbon::create(2021, 12, 14),
            ]),
            $this->generateLtcsBillingBundle($faker, [
                'id' => 11,
                'billingId' => $this->ltcsBillings[8]->id,
                'providedIn' => Carbon::create(2022, 1, 12),
            ]),
        ];
    }
}
