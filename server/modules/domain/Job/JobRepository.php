<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Job;

use Domain\Repository;
use ScalikePHP\Option;
use ScalikePHP\Seq;

/**
 * Job Repository Interface.
 */
interface JobRepository extends Repository
{
    /**
     * Lookup an entity by token from repository.
     *
     * @param string $token
     * @return \Domain\Job\Job[]&\ScalikePHP\Option
     */
    public function lookupOptionByToken(string $token): Option;

    /**
     * {@inheritdoc}
     * @return \Domain\Job\Job[]&\ScalikePHP\Seq
     */
    public function lookup(int ...$id): Seq;

    /**
     * {@inheritdoc}
     *
     * @param \Domain\Job\Job $entity
     * @return \Domain\Job\Job
     */
    public function store(mixed $entity): mixed;

    /**
     * Remove an entity from repository.
     *
     * @param \Domain\Job\Job $entity
     * @return void
     */
    public function remove(mixed $entity): void;
}
