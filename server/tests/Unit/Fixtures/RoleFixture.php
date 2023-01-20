<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Fixtures;

use Infrastructure\Role\Role;

/**
 * Role fixture.
 *
 * @mixin \Tests\Unit\Examples\ExamplesConsumer
 */
trait RoleFixture
{
    /**
     * ロール 登録.
     *
     * @return void
     */
    protected function createRoles(): void
    {
        foreach ($this->examples->roles as $entity) {
            $role = Role::fromDomain($entity)->saveIfNotExists();
            $role->syncPermissions($entity->permissions);
        }
    }
}
