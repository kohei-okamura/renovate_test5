<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Validations\Rules;

use Domain\Project\DwsProjectServiceCategory;
use Domain\Shift\ServiceOption;
use Illuminate\Support\Arr;

/**
 * 入力値の「サービスオプション」が「障害福祉サービス：計画」の「サービスオプション」として正しいことを検証する.
 *
 * @mixin \App\Validations\CustomValidator
 */
trait DwsProjectServiceOptionRule
{
    /**
     * 検証処理.
     *
     * @param string $attribute
     * @param $value
     * @param array $parameters
     * @return bool
     * @noinspection PhpUnused
     * @noinspection PhpUnusedParameterInspection
     */
    protected function validateDwsProjectServiceOption(string $attribute, $value, array $parameters): bool
    {
        if (!$this->validateServiceOption($attribute, $value, $parameters)) {
            return true;
        }
        $serviceOption = ServiceOption::from($value);

        $programIndex = $this->getExplicitKeys($attribute)[0];
        $categoryValue = Arr::get($this->data, "programs.{$programIndex}.category");
        if (!$this->validateDwsProjectServiceCategory($attribute, $categoryValue, $parameters)) {
            return true;
        }
        $category = DwsProjectServiceCategory::from($categoryValue);

        switch ($category) {
            case DwsProjectServiceCategory::physicalCare():
            case DwsProjectServiceCategory::housework():
            case DwsProjectServiceCategory::accompanyWithPhysicalCare():
            case DwsProjectServiceCategory::accompany():
                return $serviceOption === ServiceOption::sucking()
                    || $serviceOption === ServiceOption::welfareSpecialistCooperation()
                    || $serviceOption === ServiceOption::plannedByNovice()
                    || $serviceOption === ServiceOption::providedByBeginner()
                    || $serviceOption === ServiceOption::providedByCareWorkerForPwsd()
                    || $serviceOption === ServiceOption::over20()
                    || $serviceOption === ServiceOption::over50();
            case DwsProjectServiceCategory::visitingCareForPwsd():
                return $serviceOption === ServiceOption::sucking()
                    || $serviceOption === ServiceOption::behavioralDisorderSupportCooperation()
                    || $serviceOption === ServiceOption::hospitalized()
                    || $serviceOption === ServiceOption::longHospitalized()
                    || $serviceOption === ServiceOption::coaching()
                    || $serviceOption === ServiceOption::coaching();
            default:
                return false;
        }
    }
}
