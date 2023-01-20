<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Billing;

use Domain\Repository;
use ScalikePHP\Map;
use ScalikePHP\Seq;

/**
 * DwsBillingBundle Repository Interface.
 */
interface DwsBillingBundleRepository extends Repository
{
    /**
     * {@inheritdoc}
     *
     * @return \Domain\Billing\DwsBillingBundle[]&\ScalikePHP\Seq
     */
    public function lookup(int ...$id): Seq;

    /**
     * 請求ID による Lookup.
     *
     * @param int ...$ids 請求IDs
     * @return \ScalikePHP\Map key=請求ID value=Seq|\Domain\Billing\DwsBillingBundle[]
     */
    public function lookupByBillingId(int ...$ids): Map;

    /**
     * {@inheritdoc}
     *
     * @param \Domain\Billing\DwsBillingBundle $entity
     * @return \Domain\Billing\DwsBillingBundle
     */
    public function store(mixed $entity): mixed;

    /**
     * {@inheritdoc}
     *
     * @param \Domain\Billing\DwsBillingBundle $entity
     * @return void
     */
    public function remove(mixed $entity): void;
}
