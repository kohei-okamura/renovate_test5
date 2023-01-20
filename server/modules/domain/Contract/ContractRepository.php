<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Contract;

use Domain\Repository;
use ScalikePHP\Map;
use ScalikePHP\Seq;

/**
 * Contract Repository Interface.
 */
interface ContractRepository extends Repository
{
    /**
     * {@inheritdoc}
     * @return \Domain\Contract\Contract[]&\ScalikePHP\Seq
     */
    public function lookup(int ...$id): Seq;

    /**
     * 利用者IDによる取得.
     *
     * @param int ...$ids
     * @return \ScalikePHP\Map 利用者ID => Seq|Contract[]
     */
    public function lookupByUserId(int ...$ids): Map;

    /**
     * {@inheritdoc}
     *
     * @param \Domain\Contract\Contract $entity
     * @return \Domain\Contract\Contract
     */
    public function store(mixed $entity): mixed;

    /**
     * {@inheritdoc}
     *
     * @param \Domain\Contract\Contract $entity
     * @return void
     */
    public function remove(mixed $entity): void;
}
