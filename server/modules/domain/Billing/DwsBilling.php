<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Billing;

use Domain\Entity;

/**
 * 障害福祉サービス：請求.
 *
 * @property-read int $id 障害福祉サービス請求ID
 * @property-read int $organizationId 事業者ID
 * @property-read \Domain\Billing\DwsBillingOffice $office 事業所
 * @property-read \Domain\Common\Carbon $transactedIn 処理対象年月
 * @property-read \Domain\Billing\DwsBillingFile[] $files ファイル
 * @property-read \Domain\Billing\DwsBillingStatus $status 状態
 * @property-read null|\Domain\Common\Carbon $fixedAt 確定日時
 * @property-read \Domain\Common\Carbon $createdAt 登録日時
 * @property-read \Domain\Common\Carbon $updatedAt 更新日時
 */
final class DwsBilling extends Entity
{
    /** {@inheritdoc} */
    protected function attrs(): array
    {
        return [
            ...parent::attrs(),
            'organizationId',
            'office',
            'transactedIn',
            'files',
            'status',
            'fixedAt',
            'createdAt',
            'updatedAt',
        ];
    }

    /** {@inheritdoc} */
    protected function jsonables(): array
    {
        return [
            'id' => true,
            'organizationId' => true,
            'office' => true,
            'transactedIn' => true,
            'files' => true,
            'status' => true,
            'fixedAt' => true,
            'createdAt' => true,
            'updatedAt' => true,
        ];
    }
}
