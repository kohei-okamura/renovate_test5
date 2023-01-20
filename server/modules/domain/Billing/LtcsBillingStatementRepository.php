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
 * {@link \Domain\Billing\LtcsBillingStatement} Repository Interface.
 */
interface LtcsBillingStatementRepository extends Repository
{
    /**
     * {@inheritdoc}
     *
     * @return \Domain\Billing\LtcsBillingStatement[]&\ScalikePHP\Seq
     */
    public function lookup(int ...$id): Seq;

    /**
     * 親IDによるLookup.
     *
     * @param int ...$ids
     * @return \ScalikePHP\Map キー=BundleId, 値=Seq|LtcsBillingStatement[]
     */
    public function lookupByBundleId(int ...$ids): Map;

    /**
     * {@inheritdoc}
     *
     * @param \Domain\Billing\LtcsBillingStatement $entity
     * @return \Domain\Billing\LtcsBillingStatement
     */
    public function store(mixed $entity): mixed;

    /**
     * {@inheritdoc}
     *
     * @param \Domain\Billing\LtcsBillingStatement $entity
     * @return void
     */
    public function remove(mixed $entity): void;
}
