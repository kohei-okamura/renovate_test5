<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\UserBilling;

use Domain\Model;

/**
 * 利用者請求：その他サービス明細.
 *
 * @property-read int $score 単位数
 * @property-read \Domain\Common\Decimal $unitCost 単価
 * @property-read int $subtotalCost 小計
 * @property-read \Domain\Common\ConsumptionTaxRate $tax 消費税
 * @property-read int $medicalDeductionAmount 医療費控除対象額
 * @property-read int $totalAmount 合計
 * @property-read int $copayWithoutTax 自己負担額（税抜）
 * @property-read int $copayWithTax 自己負担額（税込）
 */
final class UserBillingOtherItem extends Model
{
    /** {@inheritdoc} */
    protected function attrs(): array
    {
        return [
            'score',
            'unitCost',
            'subtotalCost',
            'tax',
            'medicalDeductionAmount',
            'totalAmount',
            'copayWithoutTax',
            'copayWithTax',
        ];
    }

    /** {@inheritdoc} */
    protected function jsonables(): array
    {
        return [
            'score' => true,
            'unitCost' => true,
            'subtotalCost' => true,
            'tax' => true,
            'medicalDeductionAmount' => true,
            'totalAmount' => true,
            'copayWithoutTax' => true,
            'copayWithTax' => true,
        ];
    }
}
