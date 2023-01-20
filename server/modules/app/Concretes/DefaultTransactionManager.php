<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Concretes;

use Closure;
use Domain\TransactionManager;
use Lib\Exceptions\LogicException;

/**
 * Default Transaction Manager.
 */
final class DefaultTransactionManager implements TransactionManager
{
    /** {@inheritdoc} */
    public function run(Closure $f)
    {
        return $f();
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore 実装したらignoreを外すこと
     */
    public function rollback(Closure $f)
    {
        throw new LogicException('not implemented yet');
    }
}
