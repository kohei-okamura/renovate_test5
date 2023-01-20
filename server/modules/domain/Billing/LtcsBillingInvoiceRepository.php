<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Billing;

use Domain\Repository;
use ScalikePHP\Map;
use ScalikePHP\Seq;

/**
 * {@link \Domain\Billing\LtcsBillingInvoice} Repository Interface.
 */
interface LtcsBillingInvoiceRepository extends Repository
{
    /**
     * {@inheritdoc}
     *
     * @return \Domain\Billing\LtcsBillingInvoice[]&\ScalikePHP\Seq
     */
    public function lookup(int ...$id): Seq;

    /**
     * 親IDによるLookup.
     *
     * @param int ...$ids
     * @return \ScalikePHP\Map キー=BundleId, 値=Seq|LtcsBillingInvoice[]
     */
    public function lookupByBundleId(int ...$ids): Map;

    /**
     * {@inheritdoc}
     *
     * @param \Domain\Billing\LtcsBillingInvoice $entity
     * @return \Domain\Billing\LtcsBillingInvoice
     */
    public function store(mixed $entity): mixed;

    /**
     * {@inheritdoc}
     *
     * @param \Domain\Billing\LtcsBillingInvoice $entity
     * @return void
     */
    public function remove(mixed $entity): void;
}
