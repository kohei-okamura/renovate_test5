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
 * 指定された全ての「利用者請求」の「支払方法」が「口座振替」であるか検証する
 *
 * @mixin \App\Validations\CustomValidator
 */
trait AllPaymentMethodsAreWithdrawalRule
{
    /**
     * 検証処理.
     *
     * @param string $attribute
     * @param $value
     * @param array $parameters
     * @return bool
     * @noinspection PhpUnused
     */
    protected function validateAllPaymentMethodsAreWithdrawal(string $attribute, $value, array $parameters): bool
    {
        $this->requireParameterCount(1, $parameters, 'all_payment_methods_are_withdrawal');
        $permission = Permission::from((string)$parameters[0]);

        // 存在しない利用者請求が含まれる場合、ここではエラーとしない
        if (!$this->validateUserBillingExists($attribute, $value, [$permission])) {
            return true;
        }

        $lookupUserBillingUseCase = app(LookupUserBillingUseCase::class);
        assert($lookupUserBillingUseCase instanceof LookupUserBillingUseCase);
        $userBillings = $lookupUserBillingUseCase->handle($this->context, $permission, ...$value);

        return $userBillings->forAll(
            fn (UserBilling $x): bool => $x->user->billingDestination->paymentMethod === PaymentMethod::withdrawal()
        );
    }
}
