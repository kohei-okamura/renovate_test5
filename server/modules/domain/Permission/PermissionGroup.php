<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Permission;

use Domain\Entity;

/**
 * 権限グループ.
 *
 * @property-read string $code 権限グループコード
 * @property-read string $name 権限グループ名
 * @property-read string $displayName 表示名
 * @property-read \Domain\Permission\Permission[] $permissions 権限一覧
 * @property-read int $sortOrder 表示順
 * @property-read \Domain\Common\Carbon $createdAt 登録日時
 */
final class PermissionGroup extends Entity
{
    /** {@inheritdoc} */
    protected function attrs(): array
    {
        return [
            ...parent::attrs(),
            'code',
            'name',
            'displayName',
            'permissions',
            'sortOrder',
            'createdAt',
        ];
    }

    /** {@inheritdoc} */
    protected function jsonables(): array
    {
        return [
            'id' => true,
            'code' => true,
            'name' => true,
            'displayName' => true,
            'permissions' => true,
            'sortOrder' => false,
            'createdAt' => false,
        ];
    }
}
