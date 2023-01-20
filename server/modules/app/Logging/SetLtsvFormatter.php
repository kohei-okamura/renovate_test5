<?php

declare(strict_types=1);
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */

namespace App\Logging;

use Hikaeme\Monolog\Formatter\LtsvFormatter;
use Illuminate\Log\Logger;

/**
 * LtsvFormatter Class.
 *
 * @codeCoverageIgnore Logに関する処理はMockで行うためUnitTest除外.
 */
class SetLtsvFormatter
{
    /**
     * @param \Illuminate\Log\Logger $logger
     * @return void
     */
    public function __invoke(Logger $logger): void
    {
        foreach ($logger->getHandlers() as $handler) {
            /** @see \Hikaeme\Monolog\Formatter\LtsvFormatter::__construct() */
            $handler->setFormatter(new LtsvFormatter(
                'Y-m-d H:i:s.u',
                [
                    'datetime' => 'datetime',
                    'level_name' => 'level_name',
                    'message' => 'message',
                    'channel' => 'channel',
                ]
            ));
        }
    }
}
