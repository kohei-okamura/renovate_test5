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
 * {@link \Domain\Billing\LtcsBillingBundle} Repository Interface.
 */
interface LtcsBillingBundleRepository extends Repository
{
    /**
     * {@inheritdoc}
     *
     * @return \Domain\Billing\LtcsBillingBundle[]&\ScalikePHP\Seq
     */
    public function lookup(int ...$id): Seq;

    /**
     * 介護保険サービス：請求 ID から介護保険サービス：請求：請求単位を得る.
     *
     * @param int ...$id Billing ID
     * @return \ScalikePHP\Map Billing ID => Seq|LtcsBillingBundle[]
     */
    public function lookupByBillingId(int ...$id): Map;

    /**
     * {@inheritdoc}
     *
     * @param \Domain\Billing\LtcsBillingBundle $entity
     * @return \Domain\Billing\LtcsBillingBundle
     */
    public function store(mixed $entity): mixed;

    /**
     * {@inheritdoc}
     *
     * @param \Domain\Billing\LtcsBillingBundle $entity
     * @return void
     */
    public function remove(mixed $entity): void;
}
