<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\UserBilling;

use Domain\Repository;
use ScalikePHP\Seq;

/**
 * UserBilling Repository Interface.
 */
interface UserBillingRepository extends Repository
{
    /**
     * {@inheritdoc}
     *
     * @return \Domain\UserBilling\UserBilling[]&\ScalikePHP\Seq
     */
    public function lookup(int ...$id): Seq;

    /**
     * {@inheritdoc}
     *
     * @param \Domain\UserBilling\UserBilling $entity
     * @return \Domain\UserBilling\UserBilling
     */
    public function store(mixed $entity): mixed;

    /**
     * {@inheritdoc}
     *
     * @param \Domain\UserBilling\UserBilling $entity
     * @return void
     */
    public function remove(mixed $entity): void;
}
