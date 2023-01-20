<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Office;

use Domain\Billing\DwsServiceDivisionCode;
use Domain\Common\Carbon;
use Domain\ServiceCodeDictionary\DwsServiceCodeCategory;
use Lib\Math;
use ScalikePHP\Option;

/**
 * Support functions for {@link \Domain\Office\DwsTreatmentImprovementAdditionSupport}.
 *
 * @mixin \Domain\Office\DwsTreatmentImprovementAddition
 */
trait DwsTreatmentImprovementAdditionSupport
{
    /**
     * 「障害福祉サービス：請求：サービスコード区分」を「福祉・介護職員処遇改善加算」に変換する.
     *
     * @param \Domain\ServiceCodeDictionary\DwsServiceCodeCategory $category
     * @return \Domain\ServiceCodeDictionary\DwsServiceCodeCategory[]&\ScalikePHP\Option
     */
    public static function fromDwsServiceCodeCategory(DwsServiceCodeCategory $category): Option
    {
        switch ($category) {
            case DwsServiceCodeCategory::treatmentImprovementAddition1():
                return Option::some(self::addition1());
            case DwsServiceCodeCategory::treatmentImprovementAddition2():
                return Option::some(self::addition2());
            case DwsServiceCodeCategory::treatmentImprovementAddition3():
                return Option::some(self::addition3());
            case DwsServiceCodeCategory::treatmentImprovementAddition4():
                return Option::some(self::addition4());
            case DwsServiceCodeCategory::treatmentImprovementAddition5():
                return Option::some(self::addition5());
            case DwsServiceCodeCategory::treatmentImprovementSpecialAddition():
                return Option::some(self::specialAddition());
            default:
                return Option::none();
        }
    }

    /**
     * 福祉・介護職員処遇改善加算の単位数を計算する.
     *
     * - 2019年10月以降の係数（倍率）にのみ対応.
     * - 介護報酬改定により倍率が変わる場合は過去の倍率も返せるように改修すること.
     *
     * @param int $score
     * @param \Domain\Billing\DwsServiceDivisionCode $serviceDivisionCode
     * @param \Domain\Common\Carbon $targetDate
     * @return \Domain\Common\Decimal[]&\ScalikePHP\Option
     */
    public function compute(int $score, DwsServiceDivisionCode $serviceDivisionCode, Carbon $targetDate): Option
    {
        return match (true) {
            $targetDate >= Carbon::create(2021, 4) => $this->compute2021($score, $serviceDivisionCode),
            $targetDate >= Carbon::create(2019, 10) => $this->compute2019($score, $serviceDivisionCode),
            default => Option::none()
        };
    }

    /**
     * 福祉・介護職員処遇改善加算の単位数を計算する（2019年10月改訂版）.
     *
     * @param int $score
     * @param \Domain\Billing\DwsServiceDivisionCode $serviceDivisionCode
     * @return \Domain\Common\Decimal[]&\ScalikePHP\Option
     */
    private function compute2019(int $score, DwsServiceDivisionCode $serviceDivisionCode): Option
    {
        return match ($serviceDivisionCode) {
            DwsServiceDivisionCode::homeHelpService() => match ($this) {
                self::addition1() => Option::some(Math::round($score * 0.302)),
                self::addition2() => Option::some(Math::round($score * 0.220)),
                self::addition3() => Option::some(Math::round($score * 0.122)),
                self::addition4() => Option::some(Math::round(Math::round($score * 0.122) * 0.9)),
                self::addition5() => Option::some(Math::round(Math::round($score * 0.122) * 0.8)),
                self::specialAddition() => Option::some(Math::round($score * 0.041)),
                default => Option::none(),
            },
            DwsServiceDivisionCode::visitingCareForPwsd() => match ($this) {
                self::addition1() => Option::some(Math::round($score * 0.191)),
                self::addition2() => Option::some(Math::round($score * 0.139)),
                self::addition3() => Option::some(Math::round($score * 0.077)),
                self::addition4() => Option::some(Math::round(Math::round($score * 0.077) * 0.9)),
                self::addition5() => Option::some(Math::round(Math::round($score * 0.077) * 0.8)),
                self::specialAddition() => Option::some(Math::round($score * 0.026)),
                default => Option::none(),
            },
            default => Option::none(),
        };
    }

    /**
     * 福祉・介護職員処遇改善加算の単位数を計算する（2021年4月改訂版）.
     *
     * @param int $score
     * @param \Domain\Billing\DwsServiceDivisionCode $serviceDivisionCode
     * @return \Domain\Common\Decimal[]&\ScalikePHP\Option
     */
    private function compute2021(int $score, DwsServiceDivisionCode $serviceDivisionCode): Option
    {
        return match ($serviceDivisionCode) {
            DwsServiceDivisionCode::homeHelpService() => match ($this) {
                self::addition1() => Option::some(Math::round($score * 0.274)),
                self::addition2() => Option::some(Math::round($score * 0.200)),
                self::addition3() => Option::some(Math::round($score * 0.111)),
                self::addition4() => Option::some(Math::round(Math::round($score * 0.111) * 0.9)),
                self::addition5() => Option::some(Math::round(Math::round($score * 0.111) * 0.8)),
                self::specialAddition() => Option::some(Math::round($score * 0.041)),
                default => Option::none(),
            },
            DwsServiceDivisionCode::visitingCareForPwsd() => match ($this) {
                self::addition1() => Option::some(Math::round($score * 0.200)),
                self::addition2() => Option::some(Math::round($score * 0.146)),
                self::addition3() => Option::some(Math::round($score * 0.081)),
                self::addition4() => Option::some(Math::round(Math::round($score * 0.081) * 0.9)),
                self::addition5() => Option::some(Math::round(Math::round($score * 0.081) * 0.8)),
                self::specialAddition() => Option::some(Math::round($score * 0.026)),
                default => Option::none(),
            },
            default => Option::none(),
        };
    }
}
