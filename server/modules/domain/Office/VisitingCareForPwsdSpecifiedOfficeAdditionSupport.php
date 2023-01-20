<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Office;

use Domain\Common\Carbon;
use Lib\Math;
use ScalikePHP\Option;

/**
 * Support functions for {@link \Domain\Office\VisitingCareForPwsdSpecifiedOfficeAddition}.
 *
 * @mixin \Domain\Office\VisitingCareForPwsdSpecifiedOfficeAddition
 */
trait VisitingCareForPwsdSpecifiedOfficeAdditionSupport
{
    /**
     * 特定事業所加算の単位数を計算する.
     *
     * - 2019年10月以降の係数（倍率）にのみ対応.
     * - 介護報酬改定により倍率が変わる場合は過去の倍率も返せるように改修すること.
     *
     * @param int $score
     * @param \Domain\Common\Carbon $targetDate
     * @return \Domain\Common\Decimal[]|\ScalikePHP\Option
     */
    public function compute(int $score, Carbon $targetDate): Option
    {
        if ($targetDate >= Carbon::create(2019, 10)) {
            return $this->compute2019($score);
        } else {
            return Option::none();
        }
    }

    /**
     * 特定事業所加算区分の単位数を計算する（2019年10月改訂版）.
     *
     * @param int $score
     * @return \Domain\Common\Decimal[]|\ScalikePHP\Option
     */
    private function compute2019(int $score): Option
    {
        switch ($this) {
            case self::addition1():
                return Option::some(Math::round($score * 0.2)); // 20%
            case self::addition2():
            case self::addition3():
                return Option::some(Math::round($score * 0.1)); // 10%
            default:
                return Option::none(); // @codeCoverageIgnore
        }
    }
}
