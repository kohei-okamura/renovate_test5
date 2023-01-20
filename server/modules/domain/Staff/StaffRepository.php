<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Staff;

use Domain\Repository;
use ScalikePHP\Seq;

/**
 * Staff Repository Interface.
 */
interface StaffRepository extends Repository
{
    /**
     * {@inheritdoc}
     *
     * @return \Domain\Staff\Staff[]&\ScalikePHP\Seq
     */
    public function lookup(int ...$id): Seq;

    /**
     * {@inheritdoc}
     *
     * @param \Domain\Staff\Staff $entity
     * @return \Domain\Staff\Staff
     */
    public function store(mixed $entity): mixed;

    /**
     * {@inheritdoc}
     *
     * @param \Domain\Staff\Staff $entity
     * @return void
     */
    public function remove(mixed $entity): void;
}
