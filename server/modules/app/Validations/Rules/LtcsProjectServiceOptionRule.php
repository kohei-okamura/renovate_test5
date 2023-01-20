<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Validations\Rules;

use Domain\Project\LtcsProjectServiceCategory;
use Domain\Shift\ServiceOption;
use Illuminate\Support\Arr;

/**
 * 入力値の「サービスオプション」が「介護保険サービス：計画」の「サービスオプション」として正しいことを検証する.
 *
 * @mixin \App\Validations\CustomValidator
 */
trait LtcsProjectServiceOptionRule
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
    protected function validateLtcsProjectServiceOption(string $attribute, $value, array $parameters): bool
    {
        if (!$this->validateServiceOption($attribute, $value, $parameters)) {
            return true;
        }
        $serviceOption = ServiceOption::from($value);

        $programIndex = $this->getExplicitKeys($attribute)[0];
        $categoryValue = Arr::get($this->data, "programs.{$programIndex}.category");
        if (!$this->validateLtcsProjectServiceCategory($attribute, $categoryValue, $parameters)) {
            return true;
        }
        $category = LtcsProjectServiceCategory::from($categoryValue);

        switch ($category) {
            case LtcsProjectServiceCategory::physicalCare():
            case LtcsProjectServiceCategory::housework():
            case LtcsProjectServiceCategory::physicalCareAndHousework():
                return $serviceOption === ServiceOption::over20()
                    || $serviceOption === ServiceOption::over50()
                    || $serviceOption === ServiceOption::vitalFunctionsImprovement1()
                    || $serviceOption === ServiceOption::vitalFunctionsImprovement2();
            default:
                return false;
        }
    }
}
