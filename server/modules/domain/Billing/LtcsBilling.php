<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Billing;

use Domain\Entity;

/**
 * 介護保険サービス請求.
 *
 * @property-read int $organizationId 事業者 ID
 * @property-read \Domain\Billing\LtcsBillingOffice $office 事業所
 * @property-read \Domain\Common\Carbon $transactedIn 処理対象年月
 * @property-read \Domain\Billing\LtcsBillingFile[] $files ファイル
 * @property-read \Domain\Billing\LtcsBillingStatus $status 状態
 * @property-read null|\Domain\Common\Carbon $fixedAt 確定日時
 * @property-read \Domain\Common\Carbon $createdAt 登録日時
 * @property-read \Domain\Common\Carbon $updatedAt 更新日時
 */
final class LtcsBilling extends Entity
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
            'organizationId' => false,
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
