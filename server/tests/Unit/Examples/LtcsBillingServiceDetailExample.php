<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Examples;

use Domain\Billing\LtcsBillingServiceDetail;
use Domain\Billing\LtcsBillingServiceDetailDisposition;
use Domain\Common\Carbon;
use Domain\ProvisionReport\LtcsBuildingSubtraction;
use Domain\ServiceCode\ServiceCode;
use Domain\ServiceCodeDictionary\LtcsNoteRequirement;
use Domain\ServiceCodeDictionary\LtcsServiceCodeCategory;
use Faker\Generator;

/**
 * LtcsBillingServiceDetail Example.
 *
 * @property-read LtcsBillingServiceDetail[] $ltcsBillingServiceDetails
 * @mixin \Tests\Unit\Examples\UserExample
 */
trait LtcsBillingServiceDetailExample
{
    /**
     * 介護保険サービス：請求：サービス詳細の一覧を生成する.
     *
     * @return \Domain\Billing\LtcsBillingServiceDetail[]
     */
    protected function ltcsBillingServiceDetails(): array
    {
        return [
            $this->generateLtcsBillingServiceDetail([
                'unitScore' => 1,
                'totalScore' => 1,
                'wholeScore' => 1,
            ]),
            $this->generateLtcsBillingServiceDetail([]),
            $this->generateLtcsBillingServiceDetail([]),
        ];
    }

    /**
     * エンティティを生成する.
     *
     * @param array $overwrites
     * @return \Domain\Billing\LtcsBillingServiceDetail
     */
    protected function generateLtcsBillingServiceDetail(array $overwrites): LtcsBillingServiceDetail
    {
        /** @var \Faker\Generator $faker */
        $faker = app(Generator::class);
        $unitScore = $faker->numberBetween(1, 10);
        $x = new LtcsBillingServiceDetail(
            userId: $this->users[0]->id,
            disposition: LtcsBillingServiceDetailDisposition::result(),
            providedOn: Carbon::create(2022, 8, 4),
            serviceCode: ServiceCode::fromString('774946'),
            serviceCodeCategory: LtcsServiceCodeCategory::housework(),
            buildingSubtraction: LtcsBuildingSubtraction::subtraction2(),
            noteRequirement: LtcsNoteRequirement::durationMinutes(),
            isAddition: true,
            isLimited: true,
            durationMinutes: 47,
            unitScore: $unitScore,
            count: 1,
            wholeScore: 0,
            maxBenefitQuotaExcessScore: 0,
            maxBenefitExcessScore: 0,
            totalScore: $unitScore,
        );
        return $x->copy($overwrites);
    }
}
