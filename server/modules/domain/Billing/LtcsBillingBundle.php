<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Billing;

use Domain\Entity;

/**
 * 介護保険サービス：請求単位.
 *
 * @property-read int $billingId 請求 ID
 * @property-read \Domain\Common\Carbon $providedIn サービス提供年月
 * @property-read array|\Domain\Billing\LtcsBillingServiceDetail[] $details サービス詳細
 * @property-read \Domain\Common\Carbon $createdAt 登録日時
 * @property-read \Domain\Common\Carbon $updatedAt 更新日時
 */
final class LtcsBillingBundle extends Entity
{
    /** {@inheritdoc} */
    protected function attrs(): array
    {
        return [
            ...parent::attrs(),
            'billingId',
            'providedIn',
            'details',
            'createdAt',
            'updatedAt',
        ];
    }

    /** {@inheritdoc} */
    protected function jsonables(): array
    {
        return [
            'id' => true,
            'billingId' => true,
            'providedIn' => true,
            'details' => false,
            'createdAt' => true,
            'updatedAt' => true,
        ];
    }
}
