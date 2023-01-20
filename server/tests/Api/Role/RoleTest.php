<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Api\Role;

use Domain\Permission\Permission;
use Domain\Role\Role;
use ScalikePHP\Seq;
use Tests\Api\Test;

/**
 * /roles に関するテストの基底クラス
 */
abstract class RoleTest extends Test
{
    /**
     * パラメータ組み立て.
     *
     * @param Role $role
     * @throws \JsonException
     * @return array
     */
    protected function buildParam(Role $role): array
    {
        $param = $this->domainToArray($role);
        $param['permissions'] = Seq::fromArray($role->permissions)
            ->fold([], fn (array $z, Permission $x): array => $z + [$x->value() => true]);
        return $param;
    }
}
