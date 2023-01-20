<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Validations\Rules;

use Domain\Common\Carbon;
use Domain\DwsCertification\DwsCertificationAgreementType;
use Domain\DwsCertification\DwsLevel;
use Illuminate\Support\Arr;

/**
 * 指定された障害福祉サービス：受給者証：障害支援区分がサービス内容の組み合わせと正しいことを検証する.
 *
 * @mixin \App\Validations\CustomValidator
 */
trait DwsCertificationAgreementTypeDwsLevelRule
{
    /**
     * 検証処理.
     *
     * @param string $attribute
     * @param mixed $value
     * @param array $parameters
     * @return bool
     * @noinspection PhpUnused
     */
    protected function validateDwsCertificationAgreementTypeDwsLevel(string $attribute, $value, array $parameters): bool
    {
        $this->requireParameterCount(4, $parameters, 'dws_certification_agreement_type_dws_level');
        $index = $this->getExplicitKeys($attribute)[0];
        $effectivatedOnString = Arr::get($this->data, $parameters[0]);
        $expiredOnString = Arr::get($this->data, "agreements.{$index}.expiredOn");
        $isSubjectOfComprehensiveSupportValue = Arr::get($this->data, $parameters[3]);
        $dwsLevelValue = Arr::get($this->data, $parameters[2]);

        $hasInvalidParams = !$this->validateDate($attribute, $effectivatedOnString)
            || (!empty($expiredOnString) && !$this->validateDate($attribute, $expiredOnString))
            || !DwsLevel::isValid($dwsLevelValue)
            || !DwsCertificationAgreementType::isValid($value)
            || !$this->validateBoolean($attribute, $isSubjectOfComprehensiveSupportValue);
        // 不正なパラメータを含む場合はこのバリデーションではエラーとしない
        if ($hasInvalidParams) {
            return true;
        }

        $effectivatedOn = Carbon::parse($effectivatedOnString);
        $dwsLevel = DwsLevel::from($dwsLevelValue);
        $isSubjectOfComprehensiveSupport = (bool)$isSubjectOfComprehensiveSupportValue;
        $dwsCertificationAgreementType = DwsCertificationAgreementType::from($value);

        // 「当該契約支給量によるサービス提供終了日」が受給者証の「適用日」より前である場合はバリデーションしない
        // ※ ただし「当該契約支給量によるサービス提供終了日」が空の場合はバリデーションする
        if (!empty($expiredOnString)) {
            $expiredOn = Carbon::parse($expiredOnString);
            if ($expiredOn->lte($effectivatedOn)) {
                return true;
            }
        }

        /**
         * 「サービス内容」と「障害支援区分」「重度障害者等包括支援対象」の組み合わせが間違っている場合にエラーにする
         *
         * @link https://eustylelab-engineers.growi.cloud/zinger/設計/99.その他/障害支援区分とサービス内容
         */
        switch ($dwsCertificationAgreementType) {
            case DwsCertificationAgreementType::visitingCareForPwsd1():
                return $dwsLevel === DwsLevel::level6() && $isSubjectOfComprehensiveSupport;
            case DwsCertificationAgreementType::visitingCareForPwsd2():
                return $dwsLevel === DwsLevel::level6() && !$isSubjectOfComprehensiveSupport;
            case DwsCertificationAgreementType::visitingCareForPwsd3():
            case DwsCertificationAgreementType::outingSupportForPwsd():
                return $dwsLevel === DwsLevel::level3() || $dwsLevel === DwsLevel::level4() || $dwsLevel === DwsLevel::level5() || $dwsLevel === DwsLevel::level6();
            default:
                return true;
        }
    }
}
