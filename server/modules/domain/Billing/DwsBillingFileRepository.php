<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Billing;

use Domain\Repository;
use ScalikePHP\Option;
use ScalikePHP\Seq;

/**
 * DwsBillingFile Repository.
 */
interface DwsBillingFileRepository extends Repository
{
    /**
     * Lookup an entity by token from repository.
     *
     * @param string $token
     * @return \Domain\Billing\DwsBilling[]&\ScalikePHP\Option
     */
    public function lookupOptionByToken(string $token): Option;

    /**
     * {@inheritdoc}
     *
     * @return \Domain\Billing\DwsBillingFile[]&\ScalikePHP\Seq
     */
    public function lookup(int ...$id): Seq;

    /**
     * {@inheritdoc}
     *
     * @param \Domain\Billing\DwsBillingFile $entity
     * @return \Domain\Billing\DwsBillingFile
     */
    public function store(mixed $entity): mixed;

    /**
     * {@inheritdoc}
     *
     * @param \Domain\Billing\DwsBillingFile $entity
     * @return void
     */
    public function remove(mixed $entity): void;
}
