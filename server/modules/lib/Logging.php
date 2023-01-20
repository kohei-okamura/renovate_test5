<?php

declare(strict_types=1);
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 *  UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */

namespace Lib;

use Psr\Log\LoggerInterface;

/**
 * ログインスタンス生成処理.
 */
trait Logging
{
    /**
     * ログのインスタンスを生成する.
     *
     * @return \Psr\Log\LoggerInterface
     */
    public function logger(): LoggerInterface
    {
        return app(LoggerInterface::class);
    }
}
