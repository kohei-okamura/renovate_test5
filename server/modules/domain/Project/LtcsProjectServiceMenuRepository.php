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
 * LtcsProjectServiceMenu Repository Interface.
 */
interface LtcsProjectServiceMenuRepository extends Repository
{
    /**
     * {@inheritdoc}
     *
     * @return \Domain\Project\LtcsProjectServiceMenu[]&\ScalikePHP\Seq
     */
    public function lookup(int ...$ids): Seq;

    /**
     * {@inheritdoc}
     *
     * @param \Domain\Project\LtcsProjectServiceMenu $entity
     * @return \Domain\Project\LtcsProjectServiceMenu
     */
    public function store(mixed $entity): mixed;

    /**
     * {@inheritdoc}
     *
     * @param \Domain\Project\LtcsProjectServiceMenu $entity
     * @return void
     */
    public function remove(mixed $entity): void;
}
