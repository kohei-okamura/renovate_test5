<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Billing;

use Domain\Model;

/**
 * 障害福祉サービス請求書：明細.
 *
 * @property-read \Domain\Billing\DwsBillingPaymentCategory $paymentCategory 給付種別
 * @property-read \Domain\Billing\DwsServiceDivisionCode $serviceDivisionCode サービス種類コード
 * @property-read int $subtotalCount 件数
 * @property-read int $subtotalScore 単位数
 * @property-read int $subtotalFee 費用合計
 * @property-read int $subtotalBenefit 給付費請求額
 * @property-read int $subtotalCopay 利用者負担額
 * @property-read int $subtotalSubsidy 自治体助成額
 */
final class DwsBillingInvoiceItem extends Model
{
    /** {@inheritdoc} */
    protected function attrs(): array
    {
        return [
            'paymentCategory',
            'serviceDivisionCode',
            'subtotalCount',
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
            'paymentCategory' => true,
            'serviceDivisionCode' => true,
            'subtotalCount' => true,
            'subtotalScore' => true,
            'subtotalFee' => true,
            'subtotalBenefit' => true,
            'subtotalCopay' => true,
            'subtotalSubsidy' => true,
        ];
    }
}
