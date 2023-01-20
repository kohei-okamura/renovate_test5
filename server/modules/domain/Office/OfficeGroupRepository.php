<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Office;

use Domain\Repository;
use ScalikePHP\Seq;

/**
 * OfficeGroup Repository Interface.
 */
interface OfficeGroupRepository extends Repository
{
    /**
     * {@inheritdoc}
     * @return \Domain\Office\OfficeGroup[]&\ScalikePHP\Seq
     */
    public function lookup(int ...$id): Seq;

    /**
     * {@inheritdoc}
     *
     * @param \Domain\Office\OfficeGroup $entity
     * @return \Domain\Office\OfficeGroup
     */
    public function store(mixed $entity): mixed;

    /**
     * {@inheritdoc}
     *
     * @param \Domain\Office\OfficeGroup $entity
     * @return void
     */
    public function remove(mixed $entity): void;
}
