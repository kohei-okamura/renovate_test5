<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\User;

use Domain\Repository;
use ScalikePHP\Seq;

/**
 * User Repository Interface.
 */
interface UserRepository extends Repository
{
    /**
     * {@inheritdoc}
     * @return \Domain\User\User[]&\ScalikePHP\Seq
     */
    public function lookup(int ...$id): Seq;

    /**
     * {@inheritdoc}
     *
     * @param \Domain\User\User $entity
     * @return \Domain\User\User
     */
    public function store(mixed $entity): mixed;

    /**
     * {@inheritdoc}
     *
     * @param \Domain\User\User $entity
     * @return void
     */
    public function remove(mixed $entity): void;
}
