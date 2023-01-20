<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain;

use Closure;

/**
 * Transaction Manager Interface.
 */
interface TransactionManager
{
    /**
     * Run the transaction.
     *
     * @param \Closure $f
     * @throws \Exception|\Throwable
     * @return mixed
     */
    public function run(Closure $f);

    /**
     * Run the transaction and rollback when process terminated.
     *
     * @param \Closure $f
     * @throws \Exception|\Throwable
     * @return mixed $f の戻り値を返す
     */
    public function rollback(Closure $f);
}
