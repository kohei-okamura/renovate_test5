<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\User;

use Domain\Repository;
use ScalikePHP\Seq;

/**
 * UserDwsSubsidy Repository Interface.
 */
interface UserDwsSubsidyRepository extends Repository
{
    /**
     * {@inheritdoc}
     *
     * @return \Domain\User\UserDwsSubsidy[]&\ScalikePHP\Seq
     */
    public function lookup(int ...$id): Seq;

    /**
     * {@inheritdoc}
     *
     * @param \Domain\User\UserDwsSubsidy $entity
     * @return \Domain\User\UserDwsSubsidy
     */
    public function store(mixed $entity): mixed;

    /**
     * {@inheritdoc}
     *
     * @param \Domain\User\UserDwsSubsidy $entity
     * @return void
     */
    public function remove(mixed $entity): void;
}
