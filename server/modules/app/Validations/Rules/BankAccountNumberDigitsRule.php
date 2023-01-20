<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Validations\Rules;

use Domain\BankAccount\BankAccount;
use Illuminate\Support\Arr;

/**
 * 銀行口座の口座番号の桁数が正しいか検証する.
 *
 * @mixin \App\Validations\CustomValidator
 */
trait BankAccountNumberDigitsRule
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
    protected function validateBankAccountNumberDigits(string $attribute, $value, array $parameters): bool
    {
        $this->requireParameterCount(1, $parameters, 'bank_account_number_digits');
        $bankCode = Arr::get($this->data, $parameters[0]);
        return BankAccount::isValidBankAccountNumber($bankCode, $value);
    }
}
