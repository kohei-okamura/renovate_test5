<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Billing;

use Domain\Model;

/**
 * 障害：介護給付費等請求書：基本情報レコード：小計：介護給付費等・特別介護給付費等情報.
 *
 * @property-read int $subtotalDetailCount 小計：介護給付費等・特別介護給付費等：件数
 * @property-read int $subtotalScore 小計：介護給付費等・特別介護給付費等：単位数
 * @property-read int $subtotalFee 小計：介護給付費等・特別介護給付費等：費用合計
 * @property-read int $subtotalBenefit 小計：介護給付費等・特別介護給付費等：給付費請求額
 * @property-read int $subtotalCopay 小計：介護給付費等・特別介護給付費等：利用者負担額
 * @property-read int $subtotalSubsidy 小計：介護給付費等・特別介護給付費等：自治体助成額
 */
final class DwsBillingPayment extends Model
{
    /** {@inheritdoc} */
    protected function attrs(): array
    {
        return [
            'subtotalDetailCount',
            'subtotalScore',
            'subtotalFee',
            'subtotalBenefit',
            'subtotalCopay',
            'subtotalSubsidy',
        ];
    }

    /** {@inheritdoc} */
    protected function jsonables(): array
    {
        return [
            'subtotalDetailCount' => true,
            'subtotalScore' => true,
            'subtotalFee' => true,
            'subtotalBenefit' => true,
            'subtotalCopay' => true,
            'subtotalSubsidy' => true,
        ];
    }
}
