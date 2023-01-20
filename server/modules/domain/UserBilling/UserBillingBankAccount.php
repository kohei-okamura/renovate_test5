<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\UserBilling;

use Domain\Model;

/**
 * 利用者請求：銀行口座.
 *
 * @property-read string $bankName 銀行名
 * @property-read string $bankCode 銀行コード
 * @property-read string $bankBranchName 支店名
 * @property-read string $bankBranchCode 支店コード
 * @property-read \Domain\BankAccount\BankAccountType $bankAccountType 種別
 * @property-read string $bankAccountNumber 口座番号
 * @property-read string $bankAccountHolder 名義
 */
final class UserBillingBankAccount extends Model
{
    /**
     * 同じ銀行口座であるか判定する.
     *
     * @param \Domain\UserBilling\UserBillingBankAccount $that
     * @return bool
     */
    public function isSameBankAccount(UserBillingBankAccount $that): bool
    {
        return $this->bankCode === $that->bankCode
            && $this->bankBranchCode === $that->bankBranchCode
            && $this->bankAccountNumber === $that->bankAccountNumber
            && $this->bankAccountHolder === $that->bankAccountHolder;
    }

    /** {@inheritdoc} */
    protected function attrs(): array
    {
        return [
            'bankName',
            'bankCode',
            'bankBranchName',
            'bankBranchCode',
            'bankAccountType',
            'bankAccountNumber',
            'bankAccountHolder',
        ];
    }

    /** {@inheritdoc} */
    protected function jsonables(): array
    {
        return [
            'bankName' => true,
            'bankCode' => true,
            'bankBranchName' => true,
            'bankBranchCode' => true,
            'bankAccountType' => true,
            'bankAccountNumber' => true,
            'bankAccountHolder' => true,
        ];
    }
}
