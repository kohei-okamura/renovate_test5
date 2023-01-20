<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\DwsAreaGrade;

use Domain\Repository;
use ScalikePHP\Seq;

/**
 * DwsAreaGradeFee Repository Interface.
 */
interface DwsAreaGradeFeeRepository extends Repository
{
    /**
     * {@inheritdoc}
     *
     * @return \Domain\DwsAreaGrade\DwsAreaGradeFee[]&\ScalikePHP\Seq
     */
    public function lookup(int ...$ids): Seq;

    /**
     * {@inheritdoc}
     *
     * @param \Domain\DwsAreaGrade\DwsAreaGradeFee $entity
     * @return \Domain\DwsAreaGrade\DwsAreaGradeFee
     */
    public function store(mixed $entity): mixed;

    /**
     * {@inheritdoc}
     *
     * @param \Domain\DwsAreaGrade\DwsAreaGradeFee $entity
     * @return void
     */
    public function remove(mixed $entity): void;
}
