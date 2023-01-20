<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Permission;

use Domain\Repository;
use ScalikePHP\Seq;

/**
 * PermissionGroup Repository Interface.
 */
interface PermissionGroupRepository extends Repository
{
    /**
     * {@inheritdoc}
     *
     * @return \Domain\Permission\PermissionGroup[]&\ScalikePHP\Seq
     */
    public function lookup(int ...$id): Seq;

    /**
     * Store an entity to repository.
     *
     * @param \Domain\Permission\PermissionGroup $entity
     * @return \Domain\Permission\PermissionGroup
     */
    public function store(mixed $entity): mixed;

    /**
     * Remove an entity from repository.
     *
     * @param \Domain\Permission\PermissionGroup $entity
     * @return void
     */
    public function remove(mixed $entity): void;
}
