<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Helpers;

use ScalikePHP\Seq;

/**
 * 全ての要素に対して渡された関数が true を返すかを検査する.
 *
 * @mixin \PHPUnit\Framework\Assert
 */
trait AssertForAll
{
    /**
     * 全ての要素に対して渡された関数が true を返すかを検査する.
     *
     * @param iterable $xs
     * @param callable $p
     * @return void
     */
    protected function assertForAll(iterable $xs, callable $p): void
    {
        $this->assertTrue(Seq::fromArray($xs)->forAll($p));
    }
}
