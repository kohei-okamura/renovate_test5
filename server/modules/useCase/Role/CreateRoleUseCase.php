<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Role;

use Domain\Context\Context;
use Domain\Role\Role;

/**
 * ロール登録ユースケース.
 */
interface CreateRoleUseCase
{
    /**
     * ロールを登録する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Role\Role $role
     * @return \Domain\Role\Role
     */
    public function handle(Context $context, Role $role): Role;
}
