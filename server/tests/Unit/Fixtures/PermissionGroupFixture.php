<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Fixtures;

use Infrastructure\Permission\PermissionGroup;

/**
 * {@link \Domain\Permission\PermissionGroup} fixture.
 *
 * @mixin \Tests\Unit\Examples\ExamplesConsumer
 */
trait PermissionGroupFixture
{
    /**
     * 権限グループをデータベースに登録する.
     *
     * @return void
     */
    protected function createPermissionGroups(): void
    {
        foreach ($this->examples->permissionGroups as $entity) {
            PermissionGroup::fromDomain($entity)->saveIfNotExists()->syncPermissions($entity->permissions);
        }
    }
}
