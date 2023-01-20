<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Billing;

use Domain\Entity;

/**
 * 障害福祉サービス：請求単位.
 *
 * @property-read int $id 障害福祉サービス請求単位ID
 * @property-read int $dwsBillingId 請求ID
 * @property-read \Domain\Common\Carbon $providedIn サービス提供年月
 * @property-read string $cityCode 市町村番号
 * @property-read string $cityName 市町村名
 * @property-read array|\Domain\Billing\DwsBillingServiceDetail[] $details サービス詳細
 * @property-read \Domain\Common\Carbon $createdAt 登録日時
 * @property-read \Domain\Common\Carbon $updatedAt 更新日時
 */
final class DwsBillingBundle extends Entity
{
    /** {@inheritdoc} */
    protected function attrs(): array
    {
        return [
            ...parent::attrs(),
            'dwsBillingId',
            'providedIn',
            'cityCode',
            'cityName',
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
            'dwsBillingId' => true,
            'providedIn' => true,
            'cityCode' => true,
            'cityName' => true,
            'details' => false,
            'createdAt' => true,
            'updatedAt' => true,
        ];
    }
}
