<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Validations\Rules;

use Domain\Permission\Permission;
use Domain\UserBilling\UserBilling;
use Illuminate\Support\Arr;
use UseCase\UserBilling\LookupUserBillingUseCase;

/**
 * 繰越金額を適用した結果の請求金額が0以上であるか検証する.
 *
 * @mixin \App\Validations\CustomValidator
 */
trait UserBillingAmountIsNonNegativeRule
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
    protected function validateUserBillingAmountIsNonNegative(string $attribute, $value, array $parameters): bool
    {
        // 整数でない場合このバリデーションではエラーとしない
        if (!is_int($value)) {
            return true;
        }

        $lookupUserBillingUseCase = app(LookupUserBillingUseCase::class);
        assert($lookupUserBillingUseCase instanceof LookupUserBillingUseCase);
        $userBilling = $lookupUserBillingUseCase
            ->handle($this->context, Permission::updateUserBillings(), (int)Arr::get($this->data, 'id', -1))
            ->headOption()
            ->orNull();

        // 存在しないIDの場合、このバリデーションではエラーとしない
        if ($userBilling === null) {
            return true;
        }
        assert($userBilling instanceof UserBilling);

        // 利用者請求の totalAmount （請求金額）には繰越金額が含まれているため、現在の繰越金額を引く必要がある
        return $userBilling->totalAmount - $userBilling->carriedOverAmount + $value >= 0;
    }
}
