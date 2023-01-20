<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Providers;

use DateTimeInterface;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Database\Events\TransactionBeginning;
use Illuminate\Database\Events\TransactionCommitted;
use Illuminate\Database\Events\TransactionRolledBack;
use Illuminate\Log\LogManager;
use Illuminate\Support\ServiceProvider;
use Lib\Logging;
use ScalikePHP\Seq;

/**
 * Database QueryLog Service Provider.
 *
 * @codeCoverageIgnore テスト用の処理なのでcoverage除外
 */
class QueryLogServiceProvider extends ServiceProvider
{
    use Logging;

    private const LOG_CHANNEL = 'query';

    /**
     * Register services.
     *
     * @return void
     */
    public function register(): void
    {
        $events = app('events'); // テスト用のためDomainを利用しないEventを許可する
        $logManager = $this->logger();
        assert($logManager instanceof LogManager);
        $logger = $logManager->channel(self::LOG_CHANNEL);
        $events->listen(QueryExecuted::class, function (QueryExecuted $event) use ($logger) {
            $sql = preg_match('/\Aselect/i', $event->sql) === 1
                ? $this->getActualQuery($event->sql, $event->bindings)
                : $event->sql;
            $logger->debug('SQL', ['sql' => $sql, 'time' => "{$event->time} ms", 'file' => $this->getTrace()]);
        });
        $events->listen(TransactionBeginning::class, function () use ($logger) {
            $logger->debug('BEGIN', ['file' => $this->getTrace()]);
        });
        $events->listen(TransactionCommitted::class, function () use ($logger) {
            $logger->debug('COMMIT', ['file' => $this->getTrace()]);
        });
        $events->listen(TransactionRolledBack::class, function () use ($logger) {
            $logger->debug('ROLLBACK', ['file' => $this->getTrace()]);
        });
    }

    /**
     * クエリ中のプレースホルダを実際の値で置換する.
     *
     * @param string $query
     * @param array|mixed[] $bindings
     * @return string
     */
    private function getActualQuery(string $query, array $bindings): string
    {
        $replacements = Seq::fromArray($bindings)->map(function ($binding): string {
            if (is_string($binding)) {
                return "'{$binding}'";
            } elseif ($binding === null) {
                return 'NULL';
            } elseif ($binding === false) {
                return 'FALSE';
            } elseif ($binding === true) {
                return 'TRUE';
            } elseif ($binding instanceof DateTimeInterface) {
                return "'{$binding->format('Y-m-d H:i:s')}'";
            } else {
                return (string)$binding;
            }
        });
        $patterns = array_fill(0, $replacements->size(), '/\?/');
        return preg_replace($patterns, $replacements->toArray(), $query, 1);
    }

    /**
     * コールスタック（トレース）を取得する.
     *
     * @return string
     */
    private function getTrace(): string
    {
        return Seq::fromArray(debug_backtrace())
            ->find(function (array $trace): bool {
                return isset($trace['file'])
                    && strpos($trace['file'], \DIRECTORY_SEPARATOR . 'server' . \DIRECTORY_SEPARATOR . 'modules') !== false;
            })
            ->map(function (array $trace): string {
                return $trace['file'] . ':' . $trace['line'];
            })
            ->getOrElseValue('Unknown');
    }
}
