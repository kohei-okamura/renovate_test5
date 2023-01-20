<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Examples;

use Domain\Billing\LtcsBillingInvoice;
use Domain\Common\Carbon;
use Domain\Common\DefrayerCategory;
use Faker\Generator as FakerGenerator;
use Tests\Unit\Faker\Faker;

/**
 * {@link \Domain\Billing\LtcsBillingInvoice} Examples.
 *
 * @mixin \Tests\Unit\Examples\LtcsBillingBundleExample
 * @property-read \Domain\Billing\LtcsBillingInvoice[] $ltcsBillingInvoices
 */
trait LtcsBillingInvoiceExample
{
    /**
     * 介護保険サービス：請求書を生成する.
     *
     * @param \Faker\Generator $faker
     * @param array $attrs
     * @return \Domain\Billing\LtcsBillingInvoice
     */
    public function generateLtcsBillingInvoice(FakerGenerator $faker, array $attrs): LtcsBillingInvoice
    {
        $isSubsidy = $faker->boolean;
        $x = new LtcsBillingInvoice(
            id: null,
            billingId: $this->ltcsBillingBundles[0]->billingId,
            bundleId: $this->ltcsBillingBundles[0]->id,
            isSubsidy: $isSubsidy,
            defrayerCategory: $isSubsidy ? $faker->randomElement([null, ...DefrayerCategory::all()]) : null,
            statementCount: $faker->numberBetween(0, 100000),
            totalScore: $faker->numberBetween(0, 100000),
            totalFee: $faker->numberBetween(0, 100000),
            insuranceAmount: $faker->numberBetween(0, 100000),
            subsidyAmount: $faker->numberBetween(0, 100000),
            copayAmount: $faker->numberBetween(0, 100000),
            createdAt: Carbon::instance($faker->dateTime),
            updatedAt: Carbon::instance($faker->dateTime),
        );
        return $x->copy($attrs);
    }

    /**
     * 介護保険サービス：請求書の一覧を生成する.
     *
     * @return array|\Domain\Billing\LtcsBillingInvoice[]
     * @noinspection PhpUnused
     */
    protected function ltcsBillingInvoices(): array
    {
        $faker = Faker::make(2098052506);
        return [
            $this->generateLtcsBillingInvoice($faker, ['id' => 1]),
            $this->generateLtcsBillingInvoice($faker, ['id' => 2]),
            $this->generateLtcsBillingInvoice($faker, ['id' => 3]),
            $this->generateLtcsBillingInvoice($faker, ['id' => 4]),
            $this->generateLtcsBillingInvoice($faker, [
                'id' => 5,
                'billingId' => $this->ltcsBillingBundles[4]->billingId,
                'bundleId' => $this->ltcsBillingBundles[4]->id,
            ]),
        ];
    }
}
