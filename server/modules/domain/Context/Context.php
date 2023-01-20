<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Context;

use Domain\Permission\Permission;
use Domain\Role\RoleScope;
use ScalikePHP\Option;

/**
 * Context Interface.
 *
 * @property-read bool $isAuthenticated
 * @property-read \Domain\Organization\Organization $organization
 * @property-read \Domain\Staff\Staff[]|\ScalikePHP\Option $staff
 */
interface Context
{
    /**
     * 対象のIdにアクセス可能か判断する.
     *
     * @param array|\Domain\Permission\Permission|\Domain\Permission\Permission[] $permission
     * @param int $organizationId
     * @param iterable $officeIds
     * @param int $staffId
     * @return bool
     */
    public function isAccessibleTo(
        array|Permission $permission,
        int $organizationId,
        iterable $officeIds,
        int $staffId = 0
    ): bool;

    /**
     * 指定した権限の有無を判定する.
     *
     * @param \Domain\Permission\Permission ...$permissions
     * @return bool
     */
    public function isAuthorizedTo(Permission ...$permissions): bool;

    /**
     * ログのコンテキスト情報を返す.
     *
     * @return array
     */
    public function logContext(): array;

    /**
     * 認可されているOfficeのリストを返す.
     *
     * @param \Domain\Permission\Permission $permission
     * @return \Domain\Office\Office[]&\ScalikePHP\Option 全て認可されている場合はOption::noneが返る
     */
    public function getPermittedOffices(Permission $permission): Option;

    /**
     * 必要な権限範囲の有無を判定する.
     *
     * @param \Domain\Role\RoleScope[] $requiredScopes 必要な権限範囲
     * @return bool
     */
    public function hasRoleScope(RoleScope ...$requiredScopes): bool;

    /**
     * 完全な uri を返却する.
     *
     * @param string $path
     * @return string
     */
    public function uri(string $path): string;
}
