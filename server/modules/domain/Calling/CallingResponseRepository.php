<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Calling;

use Domain\Repository;
use ScalikePHP\Seq;

/**
 * CallingResponse Repository Interface.
 */
interface CallingResponseRepository extends Repository
{
    /**
     * {@inheritdoc}
     *
     * @return \Domain\Calling\CallingResponse[]&\ScalikePHP\Seq
     */
    public function lookup(int ...$id): Seq;

    /**
     * {@inheritdoc}
     *
     * @param \Domain\Calling\CallingResponse $entity
     * @return \Domain\Calling\CallingResponse
     */
    public function store(mixed $entity): mixed;

    /**
     * {@inheritdoc}
     *
     * @param \Domain\Calling\CallingResponse $entity
     * @return void
     */
    public function remove(mixed $entity): void;
}
