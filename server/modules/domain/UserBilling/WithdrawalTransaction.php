<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\UserBilling;

use Domain\Entity;

/**
 * 口座振替データ.
 *
 * @property-read int $organizationId 事業者 ID
 * @property-read \Domain\UserBilling\WithdrawalTransactionItem[] $items 明細
 * @property-read \Domain\Common\Carbon $deductedOn 口座振替日
 * @property-read null|\Domain\Common\Carbon $downloadedAt 最終ダウンロード日時
 * @property-read \Domain\Common\Carbon $createdAt 登録日時
 * @property-read \Domain\Common\Carbon $updatedAt 更新日時
 */
class WithdrawalTransaction extends Entity
{
    /** {@inheritdoc} */
    protected function attrs(): array
    {
        return [
            ...parent::attrs(),
            'organizationId',
            'items',
            'deductedOn',
            'downloadedAt',
            'createdAt',
            'updatedAt',
        ];
    }

    /** {@inheritdoc} */
    protected function jsonables(): array
    {
        return [
            'id' => true,
            'organizationId' => false,
            'items' => true,
            'deductedOn' => true,
            'downloadedAt' => true,
            'createdAt' => true,
            'updatedAt' => true,
        ];
    }
}
