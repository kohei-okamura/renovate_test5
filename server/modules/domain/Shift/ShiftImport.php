<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Shift;

use Domain\Entity;

/**
 * 勤務シフトインポート.
 *
 * @property-read int $organizationId 事業者ID
 * @property-read int $staffId 管理スタッフID
 * @property-read \Domain\Common\Carbon $createdAt 登録日時
 */
final class ShiftImport extends Entity
{
    /** {@inheritdoc} */
    protected function attrs(): array
    {
        return [
            ...parent::attrs(),
            'organizationId',
            'staffId',
            'createdAt',
        ];
    }

    /** {@inheritdoc} */
    protected function jsonables(): array
    {
        return [
            'id' => false,
            'organizationId' => false,
            'staffId' => false,
            'createdAt' => false,
        ];
    }
}
