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
 * StaffPasswordReset Repository Interface.
 */
interface StaffPasswordResetRepository extends Repository
{
    /**
     * {@inheritdoc}
     *
     * @return \Domain\Staff\StaffPasswordReset[]&\ScalikePHP\Seq
     */
    public function lookup(int ...$id): Seq;

    /**
     * Lookup an entity by token from repository.
     *
     * @param string $token
     * @return \Domain\Staff\StaffPasswordReset[]|\ScalikePHP\Option
     */
    public function lookupOptionByToken(string $token): Option;

    /**
     * {@inheritdoc}
     *
     * @param \Domain\Staff\StaffPasswordReset $entity
     * @return \Domain\Staff\StaffPasswordReset
     */
    public function store(mixed $entity): mixed;

    /**
     * {@inheritdoc}
     *
     * @param \Domain\Staff\StaffPasswordReset $entity
     * @return void
     */
    public function remove(mixed $entity): void;
}
