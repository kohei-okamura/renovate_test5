<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Shift;

use Domain\Repository;
use ScalikePHP\Seq;

/**
 * Shift Repository Interface.
 */
interface ShiftRepository extends Repository
{
    /**
     * {@inheritdoc}
     * @return \Domain\Shift\Shift[]&\ScalikePHP\Seq
     */
    public function lookup(int ...$id): Seq;

    /**
     * {@inheritdoc}
     *
     * @param \Domain\Shift\Shift $entity
     * @return \Domain\Shift\Shift
     */
    public function store(mixed $entity): mixed;

    /**
     * {@inheritdoc}
     *
     * @param \Domain\Shift\Shift $entity
     * @return void
     */
    public function remove(mixed $entity): void;
}
