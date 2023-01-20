<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\LtcsInsCard;

use Lib\Exceptions\LogicException;

/**
 * Support functions for {@link \Domain\LtcsInsCard\LtcsLevel}.
 *
 * @mixin \Domain\LtcsInsCard\LtcsLevel
 */
trait LtcsLevelSupport
{
    /**
     * 要介護度に応じた区分支給限度額（単位）を返す.
     *
     * @return int
     */
    public function maxBenefit(): int
    {
        return match ($this) {
            self::supportLevel1() => 5032,
            self::supportLevel2() => 10531,
            self::careLevel1() => 16765,
            self::careLevel2() => 19705,
            self::careLevel3() => 27048,
            self::careLevel4() => 30938,
            self::careLevel5() => 36217,
            default => throw new LogicException('Unexpected LtcsLevel value'),
        };
    }
}
