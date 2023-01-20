<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Examples;

use Domain\Billing\DwsBillingInvoice;
use Domain\Billing\DwsBillingPaymentCategory;
use Domain\Billing\DwsServiceDivisionCode;
use Domain\Common\Carbon;
use Faker\Generator;

/**
 * DwsBillingInvoice Examples.
 *
 * @property-read \Domain\Billing\DwsBillingInvoice[] $dwsBillingInvoices
 */
trait DwsBillingInvoiceExample
{
    /**
     * 障害福祉サービス請求書の一覧を生成する.
     *
     * @return \Domain\Billing\DwsBillingInvoice[]
     */
    protected function dwsBillingInvoices(): array
    {
        $faker = app(Generator::class);
        assert($faker instanceof Generator);
        return [
            $this->generateDwsBillingInvoice([
                'id' => 1,
                'dwsBillingBundleId' => 1,
            ]),
            $this->generateDwsBillingInvoice([
                'id' => 2,
                'dwsBillingBundleId' => 1,
            ]),
            $this->generateDwsBillingInvoice([
                'id' => 3,
                'dwsBillingBundleId' => 2,
            ]),
            $this->generateDwsBillingInvoice([
                'id' => 4,
                'dwsBillingBundleId' => 2,
                'items' => [
                    DwsBillingInvoice::item([
                        'paymentCategory' => DwsBillingPaymentCategory::category1(),
                        'serviceDivisionCode' => DwsServiceDivisionCode::homeHelpService(),
                        'subtotalCount' => $faker->numberBetween(1, 31),
                        'subtotalScore' => $faker->numberBetween(0, 10000),
                        'subtotalFee' => $faker->numberBetween(0, 1000000),
                        'subtotalBenefit' => $faker->numberBetween(0, 1000000),
                        'subtotalCopay' => $faker->numberBetween(0, 37200),
                        'subtotalSubsidy' => $faker->numberBetween(0, 100000),
                    ]),
                    DwsBillingInvoice::item([
                        'paymentCategory' => DwsBillingPaymentCategory::category1(),
                        'serviceDivisionCode' => DwsServiceDivisionCode::visitingCareForPwsd(),
                        'subtotalCount' => $faker->numberBetween(1, 31),
                        'subtotalScore' => $faker->numberBetween(0, 10000),
                        'subtotalFee' => $faker->numberBetween(0, 1000000),
                        'subtotalBenefit' => $faker->numberBetween(0, 1000000),
                        'subtotalCopay' => $faker->numberBetween(0, 37200),
                        'subtotalSubsidy' => $faker->numberBetween(0, 100000),
                    ]),
                ],
            ]),
            $this->generateDwsBillingInvoice([
                'id' => 5,
                'dwsBillingBundleId' => 6,
            ]),
        ];
    }

    /**
     * Generate an example of DwsBillingInvoice.
     *
     * @param array $overwrites
     * @return \Domain\Billing\DwsBillingInvoice
     */
    private function generateDwsBillingInvoice(array $overwrites)
    {
        $faker = app(Generator::class);
        assert($faker instanceof Generator);
        $values = [
            'claimAmount' => $faker->numberBetween(1, 999999999),
            'dwsPayment' => DwsBillingInvoice::payment([
                'subtotalDetailCount' => 10,
                'subtotalScore' => 10000,
                'subtotalFee' => 1000000,
                'subtotalBenefit' => 1000000,
                'subtotalCopay' => 37200,
                'subtotalSubsidy' => 100000,
            ]),
            'highCostDwsPayment' => DwsBillingInvoice::highCostPayment([
                'subtotalDetailCount' => 1,
                'subtotalFee' => 2,
                'subtotalBenefit' => 3,
            ]),
            'totalCount' => $faker->numberBetween(1, 31),
            'totalScore' => $faker->numberBetween(0, 10000),
            'totalFee' => $faker->numberBetween(0, 1000000),
            'totalBenefit' => $faker->numberBetween(0, 1000000),
            'totalCopay' => $faker->numberBetween(0, 37200),
            'totalSubsidy' => $faker->numberBetween(0, 100000),
            'items' => [
                DwsBillingInvoice::item([
                    'paymentCategory' => DwsBillingPaymentCategory::category1(),
                    'serviceDivisionCode' => DwsServiceDivisionCode::visitingCareForPwsd(),
                    'subtotalCount' => $faker->numberBetween(1, 31),
                    'subtotalScore' => $faker->numberBetween(0, 10000),
                    'subtotalFee' => $faker->numberBetween(0, 1000000),
                    'subtotalBenefit' => $faker->numberBetween(0, 1000000),
                    'subtotalCopay' => $faker->numberBetween(0, 37200),
                    'subtotalSubsidy' => $faker->numberBetween(0, 100000),
                ]),
            ],
            'createdAt' => Carbon::instance($faker->dateTime),
            'updatedAt' => Carbon::instance($faker->dateTime),
        ];
        return DwsBillingInvoice::create($overwrites + $values);
    }
}
