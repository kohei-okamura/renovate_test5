<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Helpers;

/**
 * 2つのモデルオブジェクトarrayが厳密に一致するかどうかを検査する（同一インスタンスかを問わない）.
 *
 * @mixin \PHPUnit\Framework\Assert
 */
trait AssertArrayStrictEquals
{
    /**
     * 渡された array の各要素を、厳密に一致するか検査する.
     *
     * @param array $expected
     * @param array $actual
     * @param string $message
     */
    protected function assertArrayStrictEquals(array $expected, array $actual, string $message = ''): void
    {
        $this->assertEach(
            function ($a, $b) use ($message): void {
                $this->assertModelStrictEquals($a, $b, $message);
            },
            $expected,
            $actual,
        );
    }
}
