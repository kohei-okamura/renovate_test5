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
 * Office Repository Interface.
 */
interface OfficeRepository extends Repository
{
    /**
     * {@inheritdoc}
     * @return \Domain\Office\Office[]&\ScalikePHP\Seq
     */
    public function lookup(int ...$id): Seq;

    /**
     * {@inheritdoc}
     *
     * @param \Domain\Office\Office $entity
     * @return \Domain\Office\Office
     */
    public function store(mixed $entity): mixed;

    /**
     * {@inheritdoc}
     *
     * @param \Domain\Office\Office $entity
     * @return void
     */
    public function remove(mixed $entity): void;
}
