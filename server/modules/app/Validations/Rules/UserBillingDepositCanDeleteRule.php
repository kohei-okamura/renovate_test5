<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Validations\Rules;

use Domain\Permission\Permission;
use Domain\User\PaymentMethod;
use Domain\UserBilling\UserBilling;
use UseCase\UserBilling\LookupUserBillingUseCase;

/**
 * 指定された利用者請求の入金日が削除可能であるか検証する.
 *
 * @mixin \App\Validations\CustomValidator
 */
trait UserBillingDepositCanDeleteRule
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
    protected function validateUserBillingDepositCanDelete(string $attribute, $value, array $parameters): bool
    {
        // 配列でない場合このバリデーションではエラーとしない
        if (!is_array($value)) {
            return true;
        }

        $lookupUserBillingUseCase = app(LookupUserBillingUseCase::class);
        assert($lookupUserBillingUseCase instanceof LookupUserBillingUseCase);
        $userBillings = $lookupUserBillingUseCase->handle($this->context, Permission::updateUserBillings(), ...$value);

        // 存在しないIDを含む場合、このバリデーションではエラーとしない
        if ($userBillings->isEmpty()) {
            return true;
        }

        return $userBillings->forAll(
            fn (UserBilling $x): bool => $x->depositedAt !== null
                && $x->user->billingDestination->paymentMethod !== PaymentMethod::withdrawal()
        );
    }
}
