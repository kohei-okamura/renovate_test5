<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\OwnExpenseProgram;

use Domain\Repository;
use ScalikePHP\Seq;

/**
 * OwnExpenseProgram Repository Interface.
 */
interface OwnExpenseProgramRepository extends Repository
{
    /**
     * {@inheritdoc}
     *
     * @return \Domain\OwnExpenseProgram\OwnExpenseProgram[]&\ScalikePHP\Seq
     */
    public function lookup(int ...$id): Seq;

    /**
     * {@inheritdoc}
     *
     * @param \Domain\OwnExpenseProgram\OwnExpenseProgram $entity
     * @return \Domain\OwnExpenseProgram\OwnExpenseProgram
     */
    public function store(mixed $entity): mixed;

    /**
     * {@inheritdoc}
     *
     * @param \Domain\OwnExpenseProgram\OwnExpenseProgram $entity
     * @return void
     */
    public function remove(mixed $entity): void;
}
