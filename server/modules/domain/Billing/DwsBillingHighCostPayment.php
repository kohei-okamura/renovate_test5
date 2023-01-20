<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Billing;

use Domain\Model;

/**
 * 障害：介護給付費等請求書：基本情報レコード：小計：特定障害者特別給付費・高額障害福祉サービス費情報.
 *
 * @property-read int $subtotalDetailCount 小計：特定障害者特別給付費・高額障害福祉サービス費：件数
 * @property-read int $subtotalFee 小計：特定障害者特別給付費・高額障害福祉サービス費：費用合計
 * @property-read int $subtotalBenefit 小計：特定障害者特別給付費・高額障害福祉サービス費：給付費請求額
 */
final class DwsBillingHighCostPayment extends Model
{
    /** {@inheritdoc} */
    protected function attrs(): array
    {
        return [
            'subtotalDetailCount',
            'subtotalFee',
            'subtotalBenefit',
        ];
    }

    /** {@inheritdoc} */
    protected function jsonables(): array
    {
        return [
            'subtotalDetailCount' => true,
            'subtotalFee' => true,
            'subtotalBenefit' => true,
        ];
    }
}
