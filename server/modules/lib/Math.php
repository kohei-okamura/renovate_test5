<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Lib;

/**
 * 算術計算ユーティリティ.
 */
final class Math
{
    /**
     * 小数点以下を切り上げる.
     *
     * @param float $x
     * @return int
     */
    public static function ceil(float $x): int
    {
        // 浮動小数点数を一度文字列に変換することで丸め誤差を回避
        // その後小数点がない、もしくは小数点以下が.0で終わっている場合は小数点以下を切り捨て。
        // 小数点以下が.0でない場合は小数点以下を切り捨てたあと切り上げとして1プラスする。
        return (bool)preg_match('/\A[^\.]+\z|(\.0)\z/', (string)$x) ? (int)(string)$x : (int)(string)$x + 1;
    }

    /**
     * 小数点以下を切り捨てる.
     *
     * @param float $x
     * @return int
     */
    public static function floor(float $x): int
    {
        // 浮動小数点数を一度文字列に変換することで丸め誤差を回避し、その後intにキャストすることで小数点以下を切り捨てる。
        return (int)((string)$x);
    }

    /**
     * 小数点以下を四捨五入する.
     *
     * @param float $x
     * @return int
     */
    public static function round(float $x): int
    {
        // 現時点では `round` で誤差が出るパターンが見当たらないため素直に `round` を用いる
        return (int)round($x);
    }
}
