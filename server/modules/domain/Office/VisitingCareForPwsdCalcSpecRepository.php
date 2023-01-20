<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Office;

use Domain\Repository;
use ScalikePHP\Seq;

/**
 * VisitingCareForPwsdCalcSpec Repository Interface.
 */
interface VisitingCareForPwsdCalcSpecRepository extends Repository
{
    /**
     * {@inheritdoc}
     * @return \Domain\Office\VisitingCareForPwsdCalcSpec[]&\ScalikePHP\Seq
     */
    public function lookup(int ...$id): Seq;

    /**
     * {@inheritdoc}
     *
     * @param \Domain\Office\VisitingCareForPwsdCalcSpec $entity
     * @return \Domain\Office\VisitingCareForPwsdCalcSpec
     */
    public function store(mixed $entity): mixed;

    /**
     * {@inheritdoc}
     *
     * @param \Domain\Office\VisitingCareForPwsdCalcSpec $entity
     * @return void
     */
    public function remove(mixed $entity): void;
}
