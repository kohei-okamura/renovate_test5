<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Helpers;

use Closure;

/**
 * 真偽値を返すクロージャーの戻り値を反転するクロージャーを生成する.
 */
trait Invert
{
    /**
     * 真偽値を返すクロージャーの戻り値を反転するクロージャーを生成する.
     *
     * @param \Closure $f
     * @return \Closure
     */
    protected function invert(Closure $f): Closure
    {
        return function () use ($f): bool {
            $args = func_get_args();
            $result = $f(...$args);
            assert(is_bool($result));
            return !$result;
        };
    }
}
