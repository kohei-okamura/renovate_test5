<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Validations\Rules;

use DateTimeInterface;
use Domain\Common\Carbon;
use Domain\Project\DwsProjectServiceCategory;
use Domain\ServiceCodeDictionary\DwsHomeHelpServiceProviderType;
use Domain\ServiceCodeDictionary\DwsServiceCodeCategory;
use Domain\Shift\ServiceOption;
use Illuminate\Support\Arr;
use ScalikePHP\Seq;

/**
 * 居宅介護において翌月分の時間が含まれていないことを検証する.
 *
 * @mixin \App\Validations\CustomValidator
 */
trait NoOvertimeRule
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
    protected function validateNoOvertime(string $attribute, $value, array $parameters): bool
    {
        $this->requireParameterCount(2, $parameters, 'no_overtime');
        $index = $this->getExplicitKeys($attribute)[0];
        $categoryValue = Arr::get($this->data, str_replace('*', $index, $parameters[0]));
        $startString = Arr::get($this->data, str_replace('*', $index, $parameters[1]));
        $options = Seq::fromArray(Arr::get($this->data, str_replace('*', $index, $parameters[2])));

        $isProvidedByCareWorkerForPwsd = $options
            ->filter(fn (int|string $option): bool => ServiceOption::isValid($option))
            ->map(fn (int $option): ServiceOption => ServiceOption::from($option))
            ->exists(fn (ServiceOption $x): bool => $x === ServiceOption::providedByCareWorkerForPwsd());

        $isValidParams = DwsProjectServiceCategory::isValid($categoryValue)
            && Carbon::canBeCreatedFromFormat($startString, DateTimeInterface::ISO8601)
            && Carbon::canBeCreatedFromFormat($value, DateTimeInterface::ISO8601);
        if (!$isValidParams) {
            return true;
        }

        $category = DwsProjectServiceCategory::from($categoryValue);
        $start = Carbon::parse($startString);
        // 居宅以外のサービスの場合はこのバリデーションではチェックしない.
        // start が月末日以外の場合はこのバリデーションではチェックしない.
        if (!$category->isHomeHelpService() || !$start->isSameDay($start->lastOfMonth())) {
            return true;
        }
        // 重研のみ区別したいのでそれ以外はそれ以外は該当なしとしておく。
        $providerType = $isProvidedByCareWorkerForPwsd
            ? DwsHomeHelpServiceProviderType::careWorkerForPwsd()
            : DwsHomeHelpServiceProviderType::none();

        $dayBoundary = DwsServiceCodeCategory::fromDwsProjectServiceCategory($category)->getDayBoundary($start, $providerType, isFirst: true);
        $end = Carbon::parse($value);
        return $end->lte($dayBoundary);
    }
}
