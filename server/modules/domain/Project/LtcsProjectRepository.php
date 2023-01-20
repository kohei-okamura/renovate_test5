<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Project;

use Domain\Repository;
use ScalikePHP\Seq;

/**
 * LtcsProject Repository Interface.
 */
interface LtcsProjectRepository extends Repository
{
    /**
     * {@inheritdoc}
     *
     * @return \Domain\Project\LtcsProject[]&\ScalikePHP\Seq
     */
    public function lookup(int ...$ids): Seq;

    /**
     * {@inheritdoc}
     *
     * @param \Domain\Project\LtcsProject $entity
     * @return \Domain\Project\LtcsProject
     */
    public function store(mixed $entity): mixed;

    /**
     * {@inheritdoc}
     *
     * @param \Domain\Project\LtcsProject $entity
     * @return void
     */
    public function remove(mixed $entity): void;
}
