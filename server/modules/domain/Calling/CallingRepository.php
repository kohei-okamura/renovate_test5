<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Calling;

use Domain\Repository;
use ScalikePHP\Option;
use ScalikePHP\Seq;

/**
 * Calling Repository Interface.
 */
interface CallingRepository extends Repository
{
    /**
     * {@inheritdoc}
     *
     * @return \Domain\Calling\Calling[]&\ScalikePHP\Seq
     */
    public function lookup(int ...$id): Seq;

    /**
     * Lookup an entity by token from repository.
     *
     * @param string $token
     * @return \Domain\Staff\StaffEmailVerification[]&\ScalikePHP\Option
     */
    public function lookupOptionByToken(string $token): Option;

    /**
     * {@inheritdoc}
     *
     * @param \Domain\Calling\Calling $entity
     * @return \Domain\Calling\Calling
     */
    public function store(mixed $entity): mixed;

    /**
     * {@inheritdoc}
     *
     * @param \Domain\Calling\Calling $entity
     * @return void
     */
    public function remove(mixed $entity): void;
}
