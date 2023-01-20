<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Validations\Rules;

use Domain\Common\Carbon;
use Domain\Office\HomeVisitLongTermCareSpecifiedOfficeAddition;
use Domain\Project\LtcsProjectServiceCategory;
use Domain\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionaryEntryFinder;
use Illuminate\Support\Arr;
use ScalikePHP\Seq;

/**
 * 入力値の「介護保険サービス：予実：サービス情報」のサービスコードが特定事業所加算区分と一致していることを検証する.
 *
 * @mixin \App\Validations\CustomValidator
 */
trait LtcsServiceCodeSpecifiedOfficeMatchRule
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
    protected function validateLtcsServiceCodeSpecifiedOfficeMatch(string $attribute, $value, array $parameters): bool
    {
        $x = Arr::get($this->data, $parameters[0]);
        // 特定事業所加算区分でない値が来た場合はここでは true
        if (!HomeVisitLongTermCareSpecifiedOfficeAddition::isValid($x)) {
            return true;
        }
        $specifiedOfficeAddition = HomeVisitLongTermCareSpecifiedOfficeAddition::from($x);
        $providedIn = Carbon::parse($parameters[1]);

        $ltcsServices = Seq::fromArray($value)
            ->filter(fn (array $x): bool => $x['category'] !== LtcsProjectServiceCategory::ownExpense()->value() && !empty($x['serviceCode']) && is_string($x['serviceCode']) && strlen($x['serviceCode']) === 6);
        // 介護保険サービスが含まれていない場合やサービスコードが不正な場合はここでは true
        if ($ltcsServices->isEmpty()) {
            return true;
        }
        /** @var LtcsHomeVisitLongTermCareDictionaryEntryFinder $finder */
        $finder = app(LtcsHomeVisitLongTermCareDictionaryEntryFinder::class);
        $serviceCodes = $ltcsServices->map(fn (array $x) => $x['serviceCode'])->distinct();
        $filterParams = ['specifiedOfficeAddition' => $specifiedOfficeAddition, 'serviceCodes' => $serviceCodes->toArray(), 'providedIn' => $providedIn];
        $paginationParams = ['all' => true, 'sortBy' => 'id'];
        return $finder->find($filterParams, $paginationParams)->list->size() === $serviceCodes->size();
    }
}
