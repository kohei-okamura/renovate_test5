<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Repository;

use App\Concretes\PermanentDatabaseTransactionManager;
use App\Concretes\TemporaryDatabaseTransactionManager;
use Illuminate\Database\Connection;
use Lib\Exceptions\LogicException;

/**
 * Repository eloquent implementation.
 */
abstract class EloquentRepository extends AbstractRepository
{
    protected const CONNECTION_PERMANENT = 'permanent';
    protected const CONNECTION_TEMPORARY = 'temporary';

    private Connection $conn;
    private string $transactionManager;

    /**
     * {@link \Infrastructure\Repository\EloquentRepository} Constructor.
     */
    public function __construct()
    {
        /** @var \Illuminate\Database\DatabaseManager $db */
        $db = app('db');
        $connection = $this->connection();
        $this->conn = $db->connection($connection);
        $this->transactionManager = self::determineTransactionManager($connection);
    }

    /** {@inheritdoc} */
    final public function transactionManager(): string
    {
        return $this->transactionManager;
    }

    /**
     * Store an entity to repository.
     *
     * @param mixed $entity
     * @throws \Throwable
     * @return mixed
     */
    final public function store(mixed $entity): mixed
    {
        return $this->conn->transaction(fn () => $this->storeInTransaction($entity));
    }

    /**
     * Remove an entity from repository.
     *
     * @param int[] $ids
     * @throws \Throwable
     * @return void
     */
    final public function removeById(int ...$ids): void
    {
        $this->conn->transaction(function () use ($ids): void {
            $this->removeByIdInTransaction(...$ids);
        });
    }

    /**
     * 接続先データベース名.
     *
     * SQLite に接続する場合は `self::CONNECTION_TEMPORARY` を返すようにオーバーライドすること.
     *
     * @return string
     */
    protected function connection(): string
    {
        return self::CONNECTION_PERMANENT;
    }

    /**
     * トランザクションマネージャーを決定する.
     *
     * @param string $connection
     * @return string
     */
    final protected static function determineTransactionManager(string $connection): string
    {
        return match ($connection) {
            self::CONNECTION_PERMANENT => PermanentDatabaseTransactionManager::class,
            self::CONNECTION_TEMPORARY => TemporaryDatabaseTransactionManager::class,
            default => throw new LogicException("Undefined connection: {$connection}"),
        };
    }

    /**
     * Store Handler.
     *
     * @param mixed $entity
     * @return mixed
     */
    abstract protected function storeInTransaction(mixed $entity): mixed;

    /**
     * remove Handler.
     *
     * @param array|int[] $ids
     */
    abstract protected function removeByIdInTransaction(int ...$ids): void;
}
