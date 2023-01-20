<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\LtcsAreaGrade;

use Domain\Repository;
use ScalikePHP\Seq;

/**
 * LtcsAreaGradeFee Repository Interface.
 */
interface LtcsAreaGradeFeeRepository extends Repository
{
    /**
     * {@inheritdoc}
     *
     * @return \Domain\LtcsAreaGrade\LtcsAreaGradeFee[]&\ScalikePHP\Seq
     */
    public function lookup(int ...$ids): Seq;

    /**
     * {@inheritdoc}
     *
     * @param \Domain\LtcsAreaGrade\LtcsAreaGradeFee $entity
     * @return \Domain\LtcsAreaGrade\LtcsAreaGradeFee
     */
    public function store(mixed $entity): mixed;

    /**
     * {@inheritdoc}
     *
     * @param \Domain\LtcsAreaGrade\LtcsAreaGradeFee $entity
     * @return void
     */
    public function remove(mixed $entity): void;
}
