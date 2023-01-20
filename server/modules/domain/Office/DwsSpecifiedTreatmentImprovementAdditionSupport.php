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
 * Support functions for {@link \Domain\Office\DwsSpecifiedTreatmentImprovementAdditionSupport}.
 *
 * @mixin \Domain\Office\DwsSpecifiedTreatmentImprovementAddition
 */
trait DwsSpecifiedTreatmentImprovementAdditionSupport
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
            case DwsServiceCodeCategory::specifiedTreatmentImprovementAddition1():
                return Option::some(self::addition1());
            case DwsServiceCodeCategory::specifiedTreatmentImprovementAddition2():
                return Option::some(self::addition2());
            default:
                return Option::none();
        }
    }

    /**
     * 福祉・介護職員特定処遇改善加算の単位数を計算する.
     *
     * - 2019年10月以降の係数（倍率）にのみ対応.
     * - 介護報酬改定により倍率が変わる場合は過去の倍率も返せるように改修すること.
     *
     * @param int $score
     * @param \Domain\Billing\DwsServiceDivisionCode $serviceDivisionCode
     * @param \Domain\Common\Carbon $targetDate
     * @return \Domain\Common\Decimal[]|\ScalikePHP\Option
     */
    public function compute(int $score, DwsServiceDivisionCode $serviceDivisionCode, Carbon $targetDate): Option
    {
        if ($targetDate >= Carbon::create(2021, 4)) {
            return $this->compute2021($score, $serviceDivisionCode);
        } elseif ($targetDate >= Carbon::create(2019, 10)) {
            return $this->compute2019($score, $serviceDivisionCode);
        } else {
            return Option::none();
        }
    }

    /**
     * 福祉・介護職員特定処遇改善加算の単位数を計算する（2019年10月改訂版）.
     *
     * @param int $score
     * @param \Domain\Billing\DwsServiceDivisionCode $serviceDivisionCode
     * @return \Domain\Common\Decimal[]|\ScalikePHP\Option
     */
    private function compute2019(int $score, DwsServiceDivisionCode $serviceDivisionCode): Option
    {
        switch ($serviceDivisionCode) {
            case DwsServiceDivisionCode::homeHelpService():
                switch ($this) {
                    case self::addition1():
                        return Option::some(Math::round($score * 0.074)); // 7.4%
                    case self::addition2():
                        return Option::some(Math::round($score * 0.058)); // 5.8%
                    default:
                        return Option::none(); // @codeCoverageIgnore
                }
            // no break
            case DwsServiceDivisionCode::visitingCareForPwsd():
                switch ($this) {
                    case self::addition1():
                        return Option::some(Math::round($score * 0.045)); // 4.5%
                    case self::addition2():
                        return Option::some(Math::round($score * 0.036)); // 3.6%
                    default:
                        return Option::none(); // @codeCoverageIgnore
                }
            // no break
            default:
                return Option::none(); // @codeCoverageIgnore
        }
    }

    /**
     * 福祉・介護職員特定処遇改善加算の単位数を計算する（2021年4月改訂版）.
     *
     * @param int $score
     * @param \Domain\Billing\DwsServiceDivisionCode $serviceDivisionCode
     * @return \Domain\Common\Decimal[]|\ScalikePHP\Option
     */
    private function compute2021(int $score, DwsServiceDivisionCode $serviceDivisionCode): Option
    {
        switch ($serviceDivisionCode) {
            case DwsServiceDivisionCode::homeHelpService():
            case DwsServiceDivisionCode::visitingCareForPwsd():
                switch ($this) {
                    case self::addition1():
                        return Option::some(Math::round($score * 0.070)); // 7.0%
                    case self::addition2():
                        return Option::some(Math::round($score * 0.055)); // 5.5%
                    default:
                        return Option::none(); // @codeCoverageIgnore
                }
            // no break
            default:
                return Option::none(); // @codeCoverageIgnore
        }
    }
}
