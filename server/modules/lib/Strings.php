<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Lib;

use Closure;
use ScalikePHP\Seq;

/**
 * 文字列ユーティリティ.
 */
final class Strings
{
    public static function mask(string $input, string $maskChar, ?Closure $p): string
    {
        return Seq::from(...preg_split('//u', $input, -1, \PREG_SPLIT_NO_EMPTY))
            ->map(fn (string $char, int $index): string => $p($char, $index) ? $maskChar : $char)
            ->mkString();
    }
}
