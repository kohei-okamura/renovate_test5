<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Office;

use Domain\Entity;

/**
 * 事業所グループ.
 *
 * @property-read int $organizationId
 * @property-read null|int $parentOfficeGroupId
 * @property-read string $name
 * @property-read int $sortOrder
 * @property-read \Domain\Common\Carbon $createdAt 登録日時
 * @property-read \Domain\Common\Carbon $updatedAt 更新日時
 */
final class OfficeGroup extends Entity
{
    /** {@inheritdoc} */
    protected function attrs(): array
    {
        return [
            ...parent::attrs(),
            'organizationId',
            'parentOfficeGroupId',
            'name',
            'sortOrder',
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
            'parentOfficeGroupId' => true,
            'name' => true,
            'sortOrder' => true,
            'createdAt' => true,
            'updatedAt' => true,
        ];
    }
}
