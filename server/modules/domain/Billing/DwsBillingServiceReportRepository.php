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
 * DwsBillingServiceReport Repository Interface.
 */
interface DwsBillingServiceReportRepository extends Repository
{
    /**
     * {@inheritdoc}
     *
     * @return \Domain\Billing\DwsBillingServiceReport[]&\ScalikePHP\Seq
     */
    public function lookup(int ...$id): Seq;

    /**
     * 請求単位IDによるLookup.
     *
     * @param int ...$ids 請求単位ID
     * @return \ScalikePHP\Map key=請求単位ID value=Seq|DwsBillingServiceReport[]
     */
    public function lookupByBundleId(int ...$ids): Map;

    /**
     * {@inheritdoc}
     *
     * @param \Domain\Billing\DwsBillingServiceReport $entity
     * @return \Domain\Billing\DwsBillingServiceReport
     */
    public function store(mixed $entity): mixed;

    /**
     * {@inheritdoc}
     *
     * @param \Domain\Billing\DwsBillingServiceReport $entity
     * @return void
     */
    public function remove(mixed $entity): void;
}
