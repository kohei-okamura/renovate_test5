<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */

namespace App\Validations\Rules;

use Domain\Common\Carbon;
use Domain\DwsCertification\DwsCertificationServiceType;
use Domain\DwsCertification\DwsLevel;
use Illuminate\Support\Arr;

/**
 * 受給者証における「介護給付費の支給決定内容」の「サービス種別」がその他の値と矛盾しないことを検証する.
 *
 * @mixin \App\Validations\CustomValidator
 */
trait DwsCertificationGrantExclusiveRule
{
    /**
     * 受給者証における「介護給付費の支給決定内容」の「サービス種別」がその他の値と矛盾しないことを検証する.
     *
     * @param string $attribute
     * @param mixed $value
     * @param array $parameters
     * @return bool
     * @noinspection PhpUnused
     */
    protected function validateDwsCertificationGrantExclusive(string $attribute, mixed $value, array $parameters): bool
    {
        // 入力値が配列ではない場合 -> バリデーションしない（通過させる）
        if (!is_array($value)) {
            return true;
        }

        // サービス種別が未入力, 正常な区分値でない場合 -> バリデーションしない（通過させる）
        $typeValue = Arr::get($value, 'dwsCertificationServiceType');
        if (empty($typeValue) || !DwsCertificationServiceType::isValid($typeValue)) {
            return true;
        }
        $type = DwsCertificationServiceType::from($typeValue);

        // 支給決定期間の終了日が未入力, または正常な日付でない場合 -> バリデーションしない（通過させる）
        $deactivatedOnValue = Arr::get($value, 'deactivatedOn');
        if (empty($deactivatedOnValue) || !$this->validateDate($attribute, $deactivatedOnValue)) {
            return true;
        }
        $deactivatedOn = Carbon::parse($deactivatedOnValue);

        // 適用日が未入力, または正常な日付でない場合 -> バリデーションしない（通過させる）
        $effectivatedOnValue = Arr::get($this->data, $parameters[0]);
        if (empty($effectivatedOnValue) || !$this->validateDate($attribute, $effectivatedOnValue)) {
            return true;
        }
        $effectivatedOn = Carbon::parse($effectivatedOnValue);

        // 支給決定期間の終了日が適用日より前（過去）である場合 -> 矛盾していても問題ないため通過
        if ($deactivatedOn < $effectivatedOn) {
            return true;
        }

        // 障害支援区分が未入力, または正常な区分値でない場合 -> バリデーションしない（通過させる）
        $levelValue = Arr::get($this->data, $parameters[1]);
        if (empty($levelValue) || !DwsLevel::isValid($levelValue)) {
            return true;
        }
        $level = DwsLevel::from($levelValue);

        $isSubjectOfComprehensiveSupportValue = Arr::get($this->data, $parameters[2]);
        $isSubjectOfComprehensiveSupport = !empty($isSubjectOfComprehensiveSupportValue);

        //
        // 重度訪問介護（重度障害者等包括支援対象者）の場合
        // -> 障害支援区分が「区分6」かつ「重度障害者等包括支援対象」が ON である場合に通過.
        //
        // 重度訪問介護（障害支援区分6該当者）の場合
        // -> 障害支援区分が「区分6」である場合に通過.
        //
        // 重度訪問介護（その他）の場合
        // -> 障害支援区分が「区分6」「区分5」「区分4」「区分3」のいずれかである場合に通過.
        //
        // 上記以外のサービス種別の場合
        // -> 無条件で通過.
        //
        return match ($type) {
            DwsCertificationServiceType::visitingCareForPwsd1() => $level === DwsLevel::level6()
                && $isSubjectOfComprehensiveSupport,
            DwsCertificationServiceType::visitingCareForPwsd2() => $level === DwsLevel::level6(),
            DwsCertificationServiceType::visitingCareForPwsd3() => match ($level) {
                DwsLevel::level6(),
                DwsLevel::level5(),
                DwsLevel::level4(),
                DwsLevel::level3() => true,
                default => false,
            },
            default => true,
        };

        return false;
    }
}
