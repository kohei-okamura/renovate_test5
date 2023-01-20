<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\User;

use Lib\Math;
use ScalikePHP\Option;

/**
 * Support functions for {@link \Domain\User\DwsUserLocationAddition}.
 *
 * @mixin \Domain\User\DwsUserLocationAddition
 */
trait DwsUserLocationAdditionSupport
{
    /**
     * 特別地域加算の単位数を計算する.
     *
     * @param int $score
     * @return \Domain\Common\Decimal[]&\ScalikePHP\Option
     */
    public function compute(int $score): Option
    {
        return match ($this) {
            self::none() => Option::none(),
            self::specifiedArea() => Option::some(Math::round($score * 0.15)), // 15%
        };
    }
}
