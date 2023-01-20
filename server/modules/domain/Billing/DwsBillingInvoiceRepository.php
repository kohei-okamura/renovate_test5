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
 * DwsBillingInvoice Repository Interface.
 */
interface DwsBillingInvoiceRepository extends Repository
{
    /**
     * {@inheritdoc}
     *
     * @return \Domain\Billing\DwsBillingInvoice[]&\ScalikePHP\Seq
     */
    public function lookup(int ...$id): Seq;

    /**
     * 請求単位IDによるLookup.
     *
     * @param int ...$ids 請求単位ID
     * @return \ScalikePHP\Map key=請求単位ID value=Seq|DwsBillingInvoice[]
     */
    public function lookupByBundleId(int ...$ids): Map;

    /**
     * {@inheritdoc}
     *
     * @param \Domain\Billing\DwsBillingInvoice $entity
     * @return \Domain\Billing\DwsBillingInvoice
     */
    public function store(mixed $entity): mixed;

    /**
     * {@inheritdoc}
     *
     * @param \Domain\Billing\DwsBillingInvoice $entity
     * @return void
     */
    public function remove(mixed $entity): void;
}
