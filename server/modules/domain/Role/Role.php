<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Role;

use Domain\Entity;

/**
 * ロール.
 *
 * @property-read int $organizationId 事業者ID
 * @property-read string $name ロール名
 * @property-read bool $isSystemAdmin システム管理者フラグ
 * @property-read \Domain\Permission\Permission[] $permissions 権限一覧
 * @property-read \Domain\Role\RoleScope $scope 権限範囲
 * @property-read int $sortOrder 表示順
 * @property-read \Domain\Common\Carbon $createdAt 登録日時
 * @property-read \Domain\Common\Carbon $updatedAt 更新日時
 */
final class Role extends Entity
{
    /** {@inheritdoc} */
    protected function attrs(): array
    {
        return [
            ...parent::attrs(),
            'organizationId',
            'name',
            'isSystemAdmin',
            'scope',
            'permissions',
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
            'name' => true,
            'isSystemAdmin' => true,
            'scope' => true,
            'permissions' => true,
            'sortOrder' => false,
            'createdAt' => true,
            'updatedAt' => true,
        ];
    }
}
