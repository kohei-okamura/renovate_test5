<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

use App\Logging\SetLtsvFormatter;
use Monolog\Handler\NullHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\TestHandler;
use Monolog\Logger;

return [
    /*
    |--------------------------------------------------------------------------
    | Default Log Channel
    |--------------------------------------------------------------------------
    |
    | This option defines the default log channel that gets used when writing
    | messages to the logs. The name specified in this option should match
    | one of the channels defined in the "channels" configuration array.
    |
    */

    'default' => env('LOG_CHANNEL', 'stdout'),

    /*
    |--------------------------------------------------------------------------
    | Log Channels
    |--------------------------------------------------------------------------
    |
    | Here you may configure the log channels for your application. Out of
    | the box, Laravel uses the Monolog PHP logging library. This gives
    | you a variety of powerful log handlers / formatters to utilize.
    |
    | Available Drivers: "single", "daily", "slack", "syslog",
    |                    "errorlog", "monolog",
    |                    "custom", "stack"
    |
    */

    'channels' => [
        // 'stack' => [
        //    'driver' => 'stack',
        //    'channels' => ['stdout'],
        // ],

        // 'single' => [
        //    'driver' => 'single',
        //    'path' => storage_path('logs/lumen.log'),
        //    'level' => 'debug',
        // ],

        // 'daily' => [
        //    'driver' => 'daily',
        //    'path' => storage_path('logs/lumen.log'),
        //    'level' => 'debug',
        //    'days' => 14,
        // ],

        // 'slack' => [
        //    'driver' => 'slack',
        //    'url' => env('LOG_SLACK_WEBHOOK_URL'),
        //    'username' => 'Lumen Log',
        //    'emoji' => ':boom:',
        //    'level' => 'critical',
        // ],

        // 'papertrail' => [
        //    'driver' => 'monolog',
        //    'level' => 'debug',
        //    'handler' => SyslogUdpHandler::class,
        //    'handler_with' => [
        //        'host' => env('PAPERTRAIL_URL'),
        //        'port' => env('PAPERTRAIL_PORT'),
        //    ],
        // ],

        // 'stderr' => [
        //    'driver' => 'monolog',
        //    'handler' => StreamHandler::class,
        //    'with' => [
        //        'stream' => 'php://stderr',
        //    ],
        // ],

        'stdout' => [
            'driver' => 'monolog',
            'handler' => StreamHandler::class,
            'tap' => [SetLtsvFormatter::class],
            'with' => [
                'stream' => 'php://stderr',
            ],
        ],

        // 'syslog' => [
        //    'driver' => 'syslog',
        //    'level' => 'debug',
        // ],

        // 'errorlog' => [
        //    'driver' => 'errorlog',
        //    'level' => 'debug',
        // ],

        'null' => [
            'driver' => 'monolog',
            'handler' => NullHandler::class,
        ],

        'query' => [
            'driver' => 'single',
            'path' => storage_path('logs/query.log'),
            'level' => 'debug',
        ],

        /**
         * https://qiita.com/kubotak/items/122b2200cf69f56a5926
         */
        'e2e' => [
            'driver' => 'custom',
            'via' => function () {
                $handler = new TestHandler();
                return new Logger('test', [$handler]);
            },
        ],
    ],
];
