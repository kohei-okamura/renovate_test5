<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Project;

use Domain\Repository;
use ScalikePHP\Seq;

/**
 * DwsProjectServiceMenu Repository Interface.
 */
interface DwsProjectServiceMenuRepository extends Repository
{
    /**
     * {@inheritdoc}
     *
     * @return \Domain\Project\DwsProjectServiceMenu[]&\ScalikePHP\Seq
     */
    public function lookup(int ...$ids): Seq;

    /**
     * {@inheritdoc}
     *
     * @param \Domain\Project\DwsProjectServiceMenu $entity
     * @return \Domain\Project\DwsProjectServiceMenu
     */
    public function store(mixed $entity): mixed;

    /**
     * {@inheritdoc}
     *
     * @param \Domain\Project\DwsProjectServiceMenu $entity
     * @return void
     */
    public function remove(mixed $entity): void;
}
