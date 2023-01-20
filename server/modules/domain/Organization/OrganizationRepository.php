<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Organization;

use Domain\Contracts\LookupOptionByCode;
use Domain\Repository;
use ScalikePHP\Option;
use ScalikePHP\Seq;

/**
 * Organization Repository Interface.
 */
interface OrganizationRepository extends Repository, LookupOptionByCode
{
    /**
     * {@inheritdoc}
     *
     * @return \Domain\Organization\Organization[]&\ScalikePHP\Seq
     */
    public function lookup(int ...$id): Seq;

    /**
     * Lookup an entity by code from repository.
     *
     * @param string $code
     * @return \Domain\Organization\OrganizationRepository[]|\ScalikePHP\Option
     */
    public function lookupOptionByCode(string $code): Option;

    /**
     * Store an entity to repository.
     *
     * @param \Domain\Organization\Organization $entity
     * @return \Domain\Organization\Organization
     */
    public function store(mixed $entity): mixed;

    /**
     * Remove an entity from repository.
     *
     * @param \Domain\Organization\Organization $entity
     * @return void
     */
    public function remove(mixed $entity): void;
}
