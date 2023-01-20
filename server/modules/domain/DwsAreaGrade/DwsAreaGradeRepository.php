<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\DwsAreaGrade;

use Domain\Repository;
use ScalikePHP\Seq;

/**
 * DwsAreaGrade Repository Interface.
 */
interface DwsAreaGradeRepository extends Repository
{
    /**
     * {@inheritdoc}
     * @return \Domain\DwsAreaGrade\DwsAreaGrade[]&\ScalikePHP\Seq
     */
    public function lookup(int ...$id): Seq;

    /**
     * {@inheritdoc}
     *
     * @param \Domain\DwsAreaGrade\DwsAreaGrade $entity
     * @return \Domain\DwsAreaGrade\DwsAreaGrade
     */
    public function store(mixed $entity): mixed;

    /**
     * {@inheritdoc}
     *
     * @param \Domain\DwsAreaGrade\DwsAreaGrade $entity
     * @return void
     */
    public function remove(mixed $entity): void;
}
