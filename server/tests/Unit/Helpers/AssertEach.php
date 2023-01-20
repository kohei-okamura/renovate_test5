<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Helpers;

use Closure;

/**
 * 渡された assertion を配列要素数回実行する.
 *
 * @mixin \PHPUnit\Framework\Assert
 */
trait AssertEach
{
    /**
     * 渡された assertion を配列要素数回実行する.
     *
     * @param \Closure $f
     * @param array ...$array
     * @return void
     */
    protected function assertEach(Closure $f, ...$array): void
    {
        $xs = array_map(null, ...$array);
        foreach ($xs as $args) {
            $f(...$args);
        }
    }
}
