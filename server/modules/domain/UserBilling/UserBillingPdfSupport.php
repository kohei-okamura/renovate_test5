<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\UserBilling;

use Domain\Common\ConsumptionTaxRate;

/**
 * 利用者請求の PDF に出力する値の作成サポート.
 */
trait UserBillingPdfSupport
{
    /**
     * 利用者請求の各値から以下の項目を算出する.
     *
     * - 医療費控除対象額
     * - 税抜金額（10%）
     * - 消費税額（10％）
     * - 税抜金額（8%）
     * - 消費税額（8％）
     * - 自己負担サービスの合計金額
     *
     * @param \Domain\UserBilling\UserBilling $billing
     * @return array
     */
    private static function calculateAmounts(UserBilling $billing): array
    {
        $medicalDeductionAmount = 0;
        $others = 0;
        $normalRateWithoutTax = 0;
        $normalRateWithTax = 0;
        $reducedRateWithoutTax = 0;
        $reducedRateWithTax = 0;
        foreach (($billing->otherItems ?? []) as $x) {
            $medicalDeductionAmount += $x->medicalDeductionAmount;
            $others += $x->totalAmount;
            if ($x->tax === ConsumptionTaxRate::ten()) {
                $normalRateWithoutTax = $x->copayWithoutTax;
                $normalRateWithTax = $x->copayWithTax;
            } elseif ($x->tax === ConsumptionTaxRate::eight()) {
                $reducedRateWithoutTax = $x->copayWithoutTax;
                $reducedRateWithTax = $x->copayWithTax;
            }
        }
        foreach ([$billing->dwsItem, $billing->ltcsItem] as $x) {
            if ($x) {
                $medicalDeductionAmount += $x->medicalDeductionAmount;
                if ($x->tax === ConsumptionTaxRate::ten()) {
                    $normalRateWithoutTax = $x->copayWithoutTax;
                    $normalRateWithTax = $x->copayWithTax;
                } elseif ($x->tax === ConsumptionTaxRate::eight()) {
                    $reducedRateWithoutTax = $x->copayWithoutTax;
                    $reducedRateWithTax = $x->copayWithTax;
                }
            }
        }

        return [
            'medicalDeductionAmount' => $medicalDeductionAmount,
            'normalTaxRate' => [
                'withoutTax' => $normalRateWithoutTax,
                'tax' => $normalRateWithTax - $normalRateWithoutTax,
            ],
            'otherItemsTotalAmount' => $others,
            'reducedTaxRate' => [
                'withoutTax' => $reducedRateWithoutTax,
                'tax' => $reducedRateWithTax - $reducedRateWithoutTax,
            ],
        ];
    }
}
