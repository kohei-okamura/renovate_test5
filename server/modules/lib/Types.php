<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Lib;

/**
 * 型に関するユーティリティ.
 */
final class Types
{
    /**
     * 変数の型を取得する.
     *
     * @param mixed $x
     * @return string
     */
    public static function getType($x): string
    {
        $type = strtolower(gettype($x));
        switch ($type) {
            case 'double':
                return 'float';
            case 'object':
                return get_class($x);
            default:
                return $type;
        }
    }
}
