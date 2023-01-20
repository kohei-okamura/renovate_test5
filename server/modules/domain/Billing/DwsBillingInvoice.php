<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Billing;

use Domain\Entity;

/**
 * 障害福祉サービス：請求書.
 *
 * @property-read int $id 障害福祉サービス請求書ID
 * @property-read int $dwsBillingBundleId 障害福祉サービス請求単位ID
 * @property-read int $claimAmount 請求金額
 * @property-read \Domain\Billing\DwsBillingPayment $dwsPayment 小計：介護給付費等・特別介護給付費等
 * @property-read \Domain\Billing\DwsBillingHighCostPayment $highCostDwsPayment 小計：特定障害者特別給付費・高額障害福祉サービス費
 * @property-read int $totalCount 合計：件数
 * @property-read int $totalScore 合計：単位数
 * @property-read int $totalFee 合計：費用合計
 * @property-read int $totalBenefit 合計：給付費請求額
 * @property-read int $totalCopay 合計：利用者負担額
 * @property-read int $totalSubsidy 合計：自治体助成額
 * @property-read array|\Domain\Billing\DwsBillingInvoiceItem[] $items 明細
 * @property-read \Domain\Common\Carbon $createdAt 登録日時
 * @property-read \Domain\Common\Carbon $updatedAt 更新日時
 */
final class DwsBillingInvoice extends Entity
{
    /**
     * 小計：介護給付費等・特別介護給付費等情報を生成する.
     *
     * @param array $values
     * @return \Domain\Billing\DwsBillingPayment
     */
    public static function payment(array $values): DwsBillingPayment
    {
        return DwsBillingPayment::create($values);
    }

    /**
     * 小計：特定障害者特別給付費・高額障害福祉サービス費情報を生成する.
     *
     * @param array $values
     * @return \Domain\Billing\DwsBillingHighCostPayment
     */
    public static function highCostPayment(array $values): DwsBillingHighCostPayment
    {
        return DwsBillingHighCostPayment::create($values);
    }

    /**
     * 障害福祉サービス請求：明細書：明細インスタンスを生成する.
     *
     * @param array $values
     * @return \Domain\Billing\DwsBillingInvoiceItem
     */
    public static function item(array $values): DwsBillingInvoiceItem
    {
        return DwsBillingInvoiceItem::create($values);
    }

    /** {@inheritdoc} */
    protected function attrs(): array
    {
        return [
            ...parent::attrs(),
            'dwsBillingBundleId',
            'claimAmount',
            'dwsPayment',
            'highCostDwsPayment',
            'totalCount',
            'totalScore',
            'totalFee',
            'totalBenefit',
            'totalCopay',
            'totalSubsidy',
            'items',
            'createdAt',
            'updatedAt',
        ];
    }

    /** {@inheritdoc} */
    protected function jsonables(): array
    {
        return [
            'id' => true,
            'dwsBillingBundleId' => true,
            'claimAmount' => true,
            'dwsPayment' => true,
            'highCostDwsPayment' => true,
            'totalCount' => true,
            'totalScore' => true,
            'totalFee' => true,
            'totalBenefit' => true,
            'totalCopay' => true,
            'totalSubsidy' => true,
            'items' => true,
            'createdAt' => true,
            'updatedAt' => true,
        ];
    }
}
