<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Validations\Rules;

use Domain\BankAccount\BankAccount;
use Domain\Permission\Permission;
use Domain\UserBilling\UserBilling;
use UseCase\UserBilling\LookupUserBillingUseCase;

/**
 * 利用者請求：銀行口座の口座番号の桁数が正しいか検証する.
 *
 * @mixin \App\Validations\CustomValidator
 */
trait UserBillingBankAccountNumberDigitsRule
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
    protected function validateUserBillingBankAccountNumberDigits(string $attribute, mixed $value, array $parameters): bool
    {
        $this->requireParameterCount(1, $parameters, 'user_billing_bank_account_number_digits');

        // パラメータ不正の場合このバリデーションではエラーとしない
        if (!is_array($value)) {
            return true;
        }

        $userBillingIds = $value;
        $permission = Permission::from((string)$parameters[0]);

        /** @var \UseCase\UserBilling\LookupUserBillingUseCase $lookupUserBillingUseCase */
        $lookupUserBillingUseCase = app(LookupUserBillingUseCase::class);

        $userBillings = $lookupUserBillingUseCase->handle($this->context, $permission, ...$userBillingIds);

        return $userBillings->forAll(fn (UserBilling $x): bool => BankAccount::isValidBankAccountNumber(
            $x->user->bankAccount->bankCode,
            $x->user->bankAccount->bankAccountNumber
        ));
    }
}
