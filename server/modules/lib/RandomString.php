<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Lib;

use Generator;
use ScalikePHP\Seq;

/**
 * ランダム文字列生成処理.
 */
final class RandomString
{
    public const ALPHABETS = 'abcdefghijklmnopqrstuvwxyz';
    public const DEFAULT_TABLE = 'abcdefghijklmnopqrstuvwxyz0123456789';
    public const NUMBERS = '0123456789';

    /**
     * ランダムな文字列を生成する.
     *
     * @param int $size
     * @param string $table
     * @return string
     */
    public static function generate(int $size, string $table): string
    {
        $f = function (string $table): Generator {
            $min = 0;
            $max = strlen($table) - 1;
            while (true) {
                yield $table[random_int($min, $max)];
            }
        };
        $g = $f($table);
        return Seq::fromTraversable($g)->take($size)->mkString();
    }

    /**
     * ランダムな文字列の {@link \ScalikePHP\Seq} を生成する.
     *
     * @param int $size
     * @param string $table
     * @return \ScalikePHP\Seq
     */
    public static function seq(int $size, string $table = self::DEFAULT_TABLE): Seq
    {
        $f = function (int $size, string $table): Generator {
            while (true) {
                yield self::generate($size, $table);
            }
        };
        $g = $f($size, $table);
        return Seq::fromTraversable($g);
    }
}
