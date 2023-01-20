<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Role;

use Domain\Repository;
use ScalikePHP\Seq;

/**
 * Role Repository Interface.
 */
interface RoleRepository extends Repository
{
    /**
     * {@inheritdoc}
     *
     * @return \Domain\Role\Role[]&\ScalikePHP\Seq
     */
    public function lookup(int ...$id): Seq;

    /**
     * Store an entity to repository.
     *
     * @param \Domain\Role\Role $entity
     * @return \Domain\Role\Role
     */
    public function store(mixed $entity): mixed;

    /**
     * Remove an entity from repository.
     *
     * @param \Domain\Role\Role $entity
     * @return void
     */
    public function remove(mixed $entity): void;
}
