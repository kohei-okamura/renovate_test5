<?php
/**
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\User;

use Domain\Repository;
use ScalikePHP\Seq;

/**
 * UserLtcsCalcSpec Repository Interface.
 */
interface UserLtcsCalcSpecRepository extends Repository
{
    /**
     * {@inheritdoc}
     *
     * @return \Domain\User\UserLtcsCalcSpec[]&\ScalikePHP\Seq
     */
    public function lookup(int ...$id): Seq;

    /**
     * {@inheritdoc}
     *
     * @param \Domain\User\UserLtcsCalcSpec $entity
     * @return \Domain\User\UserLtcsCalcSpec
     */
    public function store(mixed $entity): mixed;

    /**
     * {@inheritdoc}
     *
     * @param \Domain\User\UserLtcsCalcSpec $entity
     * @return void
     */
    public function remove(mixed $entity): void;
}
