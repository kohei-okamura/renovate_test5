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
 * {@link \Domain\Billing\LtcsBilling} Repository Interface.
 */
interface LtcsBillingRepository extends Repository
{
    /**
     * {@inheritdoc}
     *
     * @return \Domain\Billing\LtcsBilling[]&\ScalikePHP\Seq
     */
    public function lookup(int ...$id): Seq;

    /**
     * {@inheritdoc}
     *
     * @param \Domain\Billing\LtcsBilling $entity
     * @return \Domain\Billing\LtcsBilling
     */
    public function store(mixed $entity): mixed;

    /**
     * {@inheritdoc}
     *
     * @param \Domain\Billing\LtcsBilling $entity
     * @return void
     */
    public function remove(mixed $entity): void;
}
