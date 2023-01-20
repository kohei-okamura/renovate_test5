<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain;

use ScalikePHP\Seq;

/**
 * Domain Repository Interface.
 */
interface Repository
{
    /**
     * Transaction Manager's Name.
     *
     * @return string
     */
    public function transactionManager(): string;

    /**
     * Lookup an entity from repository using id.
     *
     * @param int[] $ids
     * @return \Domain\Model[]&\ScalikePHP\Seq
     */
    public function lookup(int ...$ids): Seq;

    /**
     * Store an entity to repository.
     *
     * @param mixed $entity
     * @return mixed
     */
    public function store(mixed $entity): mixed;

    /**
     * Remove an entity from repository.
     *
     * @param mixed $entity
     * @return void
     */
    public function remove(mixed $entity): void;

    /**
     * Remove an entity from repository.
     *
     * @param array|int[] $ids
     * @return void
     */
    public function removeById(int ...$ids): void;
}
