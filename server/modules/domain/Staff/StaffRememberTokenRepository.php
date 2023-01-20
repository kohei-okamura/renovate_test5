<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Staff;

use Domain\Repository;
use ScalikePHP\Option;
use ScalikePHP\Seq;

/**
 * StaffRememberToken Repository Interface.
 */
interface StaffRememberTokenRepository extends Repository
{
    /**
     * {@inheritdoc}
     *
     * @return \Domain\Staff\StaffRememberToken[]&\ScalikePHP\Seq
     */
    public function lookup(int ...$id): Seq;

    /**
     * Lookup an entity by token from repository.
     *
     * @param string $token
     * @return \Domain\Staff\StaffRememberToken[]|\ScalikePHP\Option
     */
    public function lookupOptionByToken(string $token): Option;

    /**
     * {@inheritdoc}
     *
     * @param \Domain\Staff\StaffRememberToken $entity
     * @return \Domain\Staff\StaffRememberToken
     */
    public function store(mixed $entity): mixed;

    /**
     * {@inheritdoc}
     *
     * @param \Domain\Staff\StaffRememberToken $entity
     * @return void
     */
    public function remove(mixed $entity): void;
}
