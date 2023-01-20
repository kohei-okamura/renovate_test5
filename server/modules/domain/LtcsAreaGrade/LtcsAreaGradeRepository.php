<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\LtcsAreaGrade;

use Domain\Repository;
use ScalikePHP\Seq;

/**
 * LtcsAreaGrade Repository Interface.
 */
interface LtcsAreaGradeRepository extends Repository
{
    /**
     * {@inheritdoc}
     *
     * @return \Domain\LtcsAreaGrade\LtcsAreaGrade[]&\ScalikePHP\Seq
     */
    public function lookup(int ...$id): Seq;

    /**
     * {@inheritdoc}
     *
     * @param \Domain\LtcsAreaGrade\LtcsAreaGrade $entity
     * @return \Domain\LtcsAreaGrade\LtcsAreaGrade
     */
    public function store(mixed $entity): mixed;

    /**
     * {@inheritdoc}
     *
     * @param \Domain\LtcsAreaGrade\LtcsAreaGrade $entity
     * @return void
     */
    public function remove(mixed $entity): void;
}
