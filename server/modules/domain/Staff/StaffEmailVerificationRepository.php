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
 * StaffEmailVerification Repository Interface.
 */
interface StaffEmailVerificationRepository extends Repository
{
    /**
     * {@inheritdoc}
     * @return \Domain\Staff\StaffEmailVerification[]&\ScalikePHP\Seq
     */
    public function lookup(int ...$id): Seq;

    /**
     * Lookup an entity by token from repository.
     *
     * @param string $token
     * @return \Domain\Staff\StaffEmailVerification[]|\ScalikePHP\Option
     */
    public function lookupOptionByToken(string $token): Option;

    /**
     * Store an entity to repository.
     *
     * @param \Domain\Staff\StaffEmailVerification $entity
     * @return \Domain\Staff\StaffEmailVerification
     */
    public function store(mixed $entity): mixed;

    /**
     * Remove an entity from repository.
     *
     * @param \Domain\Staff\StaffEmailVerification $entity
     * @return void
     */
    public function remove(mixed $entity): void;
}
