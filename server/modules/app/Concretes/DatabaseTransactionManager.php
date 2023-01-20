<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Concretes;

use Closure;
use Domain\TransactionManager;
use Illuminate\Database\Connection;

/**
 * データベース用トランザクション管理基底クラス.
 */
abstract class DatabaseTransactionManager implements TransactionManager
{
    private Connection $conn;

    /**
     * {@link \App\Concretes\DatabaseTransactionManager} Constructor.
     *
     * @param string $connection
     */
    public function __construct(string $connection)
    {
        /** @var \Illuminate\Database\DatabaseManager $db */
        $db = app('db');
        $this->conn = $db->connection($connection);
    }

    /** {@inheritdoc} */
    final public function run(Closure $f)
    {
        return $this->conn->transaction($f);
    }

    /** {@inheritdoc} */
    final public function rollback(Closure $f)
    {
        $this->conn->beginTransaction();
        try {
            return $f();
        } finally {
            $this->conn->rollBack();
        }
    }
}
