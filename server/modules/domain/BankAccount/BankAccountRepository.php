<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\BankAccount;

use Domain\Repository;
use ScalikePHP\Seq;

/**
 * BankAccount Repository Interface.
 */
interface BankAccountRepository extends Repository
{
    /**
     * {@inheritdoc}
     * @return \Domain\BankAccount\BankAccount[]&\ScalikePHP\Seq
     */
    public function lookup(int ...$id): Seq;

    /**
     * {@inheritdoc}
     *
     * @param \Domain\BankAccount\BankAccount $entity
     * @return \Domain\BankAccount\BankAccount
     */
    public function store(mixed $entity): mixed;

    /**
     * {@inheritdoc}
     *
     * @param \Domain\BankAccount\BankAccount $entity
     * @return void
     */
    public function remove(mixed $entity): void;
}
