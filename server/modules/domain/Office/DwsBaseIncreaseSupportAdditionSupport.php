<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
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
 * Support functions for {@link \Domain\Office\DwsBaseIncreaseSupportAdditionSupport}.
 *
 * @mixin \Domain\Office\DwsBaseIncreaseSupportAddition
 */
trait DwsBaseIncreaseSupportAdditionSupport
{
    /**
     * 福祉・介護職員等ベースアップ等支援加算の単位数を計算する.
     *
     * - 2022年10月改定版のみ対応.
     * - 介護報酬改定により倍率が変わる場合は過去の倍率も返せるように改修すること.
     *
     * @param int $score
     * @param \Domain\Billing\DwsServiceDivisionCode $serviceDivisionCode
     * @param \Domain\Common\Carbon $targetDate
     * @return \Domain\Common\Decimal[]&\ScalikePHP\Option
     */
    public function compute(int $score, DwsServiceDivisionCode $serviceDivisionCode, Carbon $targetDate): Option
    {
        if ($targetDate >= Carbon::create(2022, 10)) {
            return match ($serviceDivisionCode) {
                // 現状居宅と重訪は同じ加算率なので共通の処理で計算する.
                DwsServiceDivisionCode::homeHelpService(),
                DwsServiceDivisionCode::visitingCareForPwsd() => match ($this) {
                    self::addition1() => Option::some(Math::round($score * 0.045)),
                    default => Option::none(),
                },
                default => Option::none(),// @codeCoverageIgnore
            };
        } else {
            return Option::none();
        }
    }

    /**
     * 「障害福祉サービス：請求：サービスコード区分」を「障害福祉サービス：ベースアップ等支援加算」に変換する.
     *
     * @param \Domain\ServiceCodeDictionary\DwsServiceCodeCategory $category
     * @return \Domain\Office\DwsBaseIncreaseSupportAddition[]&\ScalikePHP\Option
     */
    public static function fromDwsServiceCodeCategory(DwsServiceCodeCategory $category): Option
    {
        return match ($category) {
            DwsServiceCodeCategory::baseIncreaseSupportAddition() => Option::some(self::addition1()),
            default => Option::none(),
        };
    }
}
