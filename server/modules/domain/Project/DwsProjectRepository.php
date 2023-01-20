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
 * DwsProject Repository Interface.
 */
interface DwsProjectRepository extends Repository
{
    /**
     * {@inheritdoc}
     *
     * @return \Domain\Project\DwsProject[]&\ScalikePHP\Seq
     */
    public function lookup(int ...$ids): Seq;

    /**
     * {@inheritdoc}
     *
     * @param \Domain\Project\DwsProject $entity
     * @return \Domain\Project\DwsProject
     */
    public function store(mixed $entity): mixed;

    /**
     * {@inheritdoc}
     *
     * @param \Domain\Project\DwsProject $entity
     * @return void
     */
    public function remove(mixed $entity): void;
}
