<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Office;

use Domain\Common\Carbon;
use Domain\ServiceCodeDictionary\DwsServiceCodeCategory;
use Lib\Math;
use ScalikePHP\Option;

/**
 * Support functions for {@link \Domain\Office\HomeHelpServiceSpecifiedOfficeAddition}.
 *
 * @mixin \Domain\Office\HomeHelpServiceSpecifiedOfficeAddition
 */
trait HomeHelpServiceSpecifiedOfficeAdditionSupport
{
    /**
     * 「障害福祉サービス：請求：サービスコード区分」を「障害福祉サービス：居宅介護：特定事業所加算区分」に変換する.
     *
     * @param \Domain\ServiceCodeDictionary\DwsServiceCodeCategory $category
     * @return \Domain\ServiceCodeDictionary\DwsServiceCodeCategory[]|\ScalikePHP\Option
     */
    public static function fromDwsServiceCodeCategory(DwsServiceCodeCategory $category): Option
    {
        switch ($category) {
            case $category === DwsServiceCodeCategory::specifiedOfficeAddition1():
                return Option::some(self::addition1());
            case $category === DwsServiceCodeCategory::specifiedOfficeAddition2():
                return Option::some(self::addition2());
            case $category === DwsServiceCodeCategory::specifiedOfficeAddition3():
                return Option::some(self::addition3());
            case $category === DwsServiceCodeCategory::specifiedOfficeAddition4():
                return Option::some(self::addition4());
            default:
                return Option::none();
        }
    }

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
            case self::addition4():
                return Option::some(Math::round($score * 0.05)); // 5%
            default:
                return Option::none(); // @codeCoverageIgnore
        }
    }
}
