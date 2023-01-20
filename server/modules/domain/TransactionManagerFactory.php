<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain;

/**
 * Transaction Manager Factory Interface.
 */
interface TransactionManagerFactory
{
    /**
     * Factory Transaction Manager.
     *
     * @param \Domain\Repository[] $repositories
     * @return \Domain\TransactionManager
     */
    public function factory(Repository ...$repositories): TransactionManager;
}
