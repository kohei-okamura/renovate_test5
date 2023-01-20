<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\LtcsInsCard;

use Domain\Repository;
use ScalikePHP\Map;
use ScalikePHP\Seq;

/**
 * LtcsInsCard Repository Interface.
 */
interface LtcsInsCardRepository extends Repository
{
    /**
     * {@inheritdoc}
     *
     * @return \Domain\LtcsInsCard\LtcsInsCard[]&\ScalikePHP\Seq
     */
    public function lookup(int ...$id): Seq;

    /**
     * 利用者IDによるLookup.
     *
     * @param int ...$ids 利用者ID
     * @return \ScalikePHP\Map key=利用者ID value=Seq|LtcsInsCard[]
     */
    public function lookupByUserId(int ...$ids): Map;

    /**
     * {@inheritdoc}
     *
     * @param \Domain\LtcsInsCard\LtcsInsCard $entity
     * @return \Domain\LtcsInsCard\LtcsInsCard
     */
    public function store(mixed $entity): mixed;

    /**
     * {@inheritdoc}
     *
     * @param \Domain\LtcsInsCard\LtcsInsCard $entity
     * @return void
     */
    public function remove(mixed $entity): void;
}
