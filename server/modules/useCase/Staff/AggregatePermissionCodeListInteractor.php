<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Staff;

use Domain\Context\Context;
use Domain\Permission\Permission;
use Domain\Role\Role;
use ScalikePHP\Seq;

/**
 * 権限コード一覧取得ユースケース実装.
 */
class AggregatePermissionCodeListInteractor implements AggregatePermissionCodeListUseCase
{
    /** {@inheritdoc} */
    public function handle(Context $context, Seq $roles): array
    {
        if ($this->systemAdminExists($roles)) {
            return Permission::all();
        } else {
            return $roles
                ->flatMap(fn (Role $role) => $role->permissions)
                ->distinctBy(fn (Permission $permission) => $permission->value())
                ->toArray();
        }
    }

    /**
     * 指定したロールにシステム管理者のロールが含まれるか.
     *
     * @param \Domain\Role\Role[]|\ScalikePHP\Seq $roles
     * @return bool
     */
    private function systemAdminExists(Seq $roles): bool
    {
        return $roles->exists(fn (Role $role) => $role->isSystemAdmin);
    }
}
