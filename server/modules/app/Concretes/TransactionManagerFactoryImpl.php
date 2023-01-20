<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Concretes;

use Domain\Repository;
use Domain\TransactionManager;
use Domain\TransactionManagerFactory;
use ScalikePHP\Seq;

/**
 * TransactionManagerFactory 実装.
 */
final class TransactionManagerFactoryImpl implements TransactionManagerFactory
{
    /** {@inheritdoc} */
    public function factory(Repository ...$repositories): TransactionManager
    {
        $xs = Seq::fromArray($repositories)
            ->map(fn (Repository $repository) => $repository->transactionManager())
            ->distinct()
            ->map(fn (string $name) => app($name));
        return $xs->size() === 1
            ? $xs->head()
            : $xs->fold(
                new DefaultTransactionManager(),
                fn (TransactionManager $z, TransactionManager $x) => ComposedTransactionManager::compose($z, $x)
            );
    }
}
