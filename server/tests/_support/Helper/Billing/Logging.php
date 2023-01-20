<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Helper\Billing;

use Codeception\Module;
use Lib\Json;
use Lib\Logging as LibLogging;
use Monolog\Handler\TestHandler;
use Psr\Log\LogLevel;

/**
 * API Test Helper Class for Logging.
 */
class Logging extends Module
{
    use LibLogging;

    /**
     * 出力されたログ件数の確認.
     *
     * @param int $expected 件数
     */
    public function seeLogCountExec(int $expected): void
    {
        $logger = $this->logger();
        $handlers = $logger->getHandlers();
        assert($handlers[0] instanceof TestHandler);
        $records = $handlers[0]->getRecords();
        codecept_debug('[LOG]: count=' . count($records));
        $this->assertCount(
            $expected,
            $records,
            count($records) === 0
                ? 'No Logs'
                : 'Actual Logs: ' . json_encode($records, \JSON_UNESCAPED_UNICODE)
        );
    }

    /**
     * 出力したログの確認.
     *
     * @param int $pos ログ順番
     * @param string $level ログレベル
     * @param string $message ログメッセージ
     * @param array $context ログコンテキスト
     */
    public function seeLogMessageExec(int $pos, string $level, string $message, array $context = []): void
    {
        $handlers = $this->logger()->getHandlers();
        assert($handlers[0] instanceof TestHandler);
        $records = $handlers[0]->getRecords();

        codecept_debug("[LOG][{$pos}]: LEVEL={$records[$pos]['level_name']}");
        codecept_debug("[LOG][{$pos}]: Message={$records[$pos]['message']}");
        codecept_debug("[LOG][{$pos}]: Context=" . Json::encode($records[$pos]['context']));

        if ($level === LogLevel::INFO && count($records[$pos]['context']) > 0) {
            $this->assertNotEquals(
                [],
                $context,
                'contextがAssertされていない. actual: ' . Json::encode($records[$pos]['context'])
            );
        }

        $this->assertEquals(strtoupper($level), $records[$pos]['level_name']);
        $this->assertEquals($message, $records[$pos]['message']);
        foreach ($context as $key => $val) {
            $this->assertArrayHasKey($key, $records[$pos]['context'], "Key Not Found={$key}");
            if ($val !== '*') {
                $this->assertEquals(
                    $val,
                    $records[$pos]['context'][$key],
                    "Key={$key}, Failed asserting Value is [{$records[$pos]['context'][$key]}], expected=[{$val}]"
                );
            }
        }
    }
}
