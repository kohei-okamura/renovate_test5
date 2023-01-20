<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain;

/**
 * 等価性判断インターフェース.
 */
interface Equatable
{
    /**
     * 指定された値とオブジェクト自身が等しいかどうかを判定する.
     *
     * @param mixed $that
     * @return bool
     */
    public function equals(mixed $that): bool;
}
