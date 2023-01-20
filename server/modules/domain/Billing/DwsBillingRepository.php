<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Billing;

use Domain\Repository;
use ScalikePHP\Seq;

/**
 * DwsBilling Repository Interface.
 */
interface DwsBillingRepository extends Repository
{
    /**
     * {@inheritdoc}
     *
     * @return \Domain\Billing\DwsBilling[]&\ScalikePHP\Seq
     */
    public function lookup(int ...$id): Seq;

    /**
     * {@inheritdoc}
     *
     * @param \Domain\Billing\DwsBilling $entity
     * @return \Domain\Billing\DwsBilling
     */
    public function store(mixed $entity): mixed;

    /**
     * {@inheritdoc}
     *
     * @param \Domain\Billing\DwsBilling $entity
     * @return void
     */
    public function remove(mixed $entity): void;
}
