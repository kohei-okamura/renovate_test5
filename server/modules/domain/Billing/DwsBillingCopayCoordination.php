<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Billing;

use Domain\Entity;

/**
 * 利用者負担上限額管理結果票.
 *
 * @property-read int $id 利用者負担上限額管理結果票ID
 * @property-read int $dwsBillingId 障害福祉サービス請求ID
 * @property-read int $dwsBillingBundleId 障害福祉サービス請求単位ID
 * @property-read \Domain\Billing\DwsBillingOffice $office 上限管理事業所
 * @property-read \Domain\Billing\DwsBillingUser $user 上限管理対象利用者
 * @property-read \Domain\Billing\CopayCoordinationResult $result 利用者負担上限額管理結果
 * @property-read \Domain\Billing\DwsBillingCopayCoordinationExchangeAim $exchangeAim 作成区分
 * @property-read \Domain\Billing\DwsBillingCopayCoordinationPayment $total 合計
 * @property-read \Domain\Billing\DwsBillingCopayCoordinationItem[] $items 明細
 * @property-read \Domain\Billing\DwsBillingStatus $status 状態
 * @property-read \Domain\Common\Carbon $createdAt 登録日時
 * @property-read \Domain\Common\Carbon $updatedAt 更新日時
 */
final class DwsBillingCopayCoordination extends Entity
{
    /** {@inheritdoc} */
    protected function attrs(): array
    {
        return [
            ...parent::attrs(),
            'dwsBillingId',
            'dwsBillingBundleId',
            'office',
            'user',
            'result',
            'exchangeAim',
            'items',
            'total',
            'status',
            'createdAt',
            'updatedAt',
        ];
    }

    /** {@inheritdoc} */
    protected function jsonables(): array
    {
        return [
            'id' => true,
            'dwsBillingId' => true,
            'dwsBillingBundleId' => true,
            'office' => true,
            'user' => true,
            'result' => true,
            'exchangeAim' => true,
            'items' => true,
            'total' => true,
            'status' => true,
            'createdAt' => true,
            'updatedAt' => true,
        ];
    }
}
