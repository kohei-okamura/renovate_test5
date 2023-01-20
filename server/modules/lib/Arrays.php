<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Lib;

use Closure;

/**
 * 配列ユーティリティ.
 */
final class Arrays
{
    /**
     * ジェネレータを使って配列を生成する.
     *
     * @param \Closure $f ジェネレータを返す関数
     * @return array
     */
    public static function generate(Closure $f): array
    {
        $g = call_user_func($f);
        return $g ? iterator_to_array($g) : [];
    }
}
