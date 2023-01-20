<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\DwsCertification;

use Domain\Repository;
use ScalikePHP\Map;
use ScalikePHP\Seq;

/**
 * DwsCertification Repository Interface.
 */
interface DwsCertificationRepository extends Repository
{
    /**
     * {@inheritdoc}
     *
     * @param int ...$id
     * @return \Domain\DwsCertification\DwsCertification[]&\ScalikePHP\Seq
     */
    public function lookup(int ...$id): Seq;

    /**
     * 利用者IDによるLookup.
     *
     * @param int ...$ids 利用者ID
     * @return \ScalikePHP\Map key=利用者ID value=Seq|DwsCertification[]
     */
    public function lookupByUserId(int ...$ids): Map;

    /**
     * {@inheritdoc}
     *
     * @param \Domain\DwsCertification\DwsCertification $entity
     * @return \Domain\DwsCertification\DwsCertification
     */
    public function store(mixed $entity): mixed;

    /**
     * {@inheritdoc}
     *
     * @param \Domain\DwsCertification\DwsCertification $entity
     * @return void
     */
    public function remove(mixed $entity): void;
}
