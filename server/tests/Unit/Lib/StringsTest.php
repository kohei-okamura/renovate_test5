<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Lib;

use Lib\Strings;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Test;

/**
 * {@link \Lib\Strings} のテスト.
 */
final class StringsTest extends Test
{
    use UnitSupport;

    /**
     * @test
     * @return void
     */
    public function describe_mask(): void
    {
        $this->should('return masked string', function (): void {
            $str = '成歩堂 龍ノ介';
            $spaceCount = 0;
            $actual = Strings::mask($str, '●', function (string $char, int $index) use (&$spaceCount): bool {
                if ($char === ' ') {
                    ++$spaceCount;
                    return false;
                }
                return ($index - $spaceCount) % 2 === 1;
            });
            $expected = '成●堂 ●ノ●';

            $this->assertSame($expected, $actual);
        });
    }
}
