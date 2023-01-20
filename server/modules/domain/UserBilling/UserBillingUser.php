<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\UserBilling;

use Domain\BankAccount\BankAccount;
use Domain\Model;
use Domain\User\User;

/**
 * 利用者請求：利用者.
 *
 * @property-read \Domain\Common\StructuredName $name 氏名
 * @property-read \Domain\Common\Addr $addr 住所
 * @property-read \Domain\Common\Contact[] $contacts 連絡先電話番号
 * @property-read \Domain\User\UserBillingDestination $billingDestination 請求先情報
 * @property-read \Domain\UserBilling\UserBillingBankAccount $bankAccount 銀行口座
 */
final class UserBillingUser extends Model
{
    /**
     * 利用者請求：利用者 ドメインモデルを作成する.
     *
     * @param \Domain\User\User $user
     * @param \Domain\BankAccount\BankAccount $bankAccount
     * @return \Domain\UserBilling\UserBillingUser
     */
    public static function from(User $user, BankAccount $bankAccount): self
    {
        return self::create([
            'name' => $user->name,
            'addr' => $user->addr,
            'contacts' => $user->contacts,
            'billingDestination' => $user->billingDestination,
            'bankAccount' => UserBillingBankAccount::create([
                'bankName' => $bankAccount->bankName,
                'bankCode' => $bankAccount->bankCode,
                'bankBranchName' => $bankAccount->bankBranchName,
                'bankBranchCode' => $bankAccount->bankBranchCode,
                'bankAccountType' => $bankAccount->bankAccountType,
                'bankAccountNumber' => $bankAccount->bankAccountNumber,
                'bankAccountHolder' => $bankAccount->bankAccountHolder,
            ]),
        ]);
    }

    /** {@inheritdoc} */
    protected function attrs(): array
    {
        return [
            'name',
            'addr',
            'contacts',
            'billingDestination',
            'bankAccount',
        ];
    }

    /** {@inheritdoc} */
    protected function jsonables(): array
    {
        return [
            'name' => true,
            'addr' => true,
            'contacts' => true,
            'billingDestination' => true,
            'bankAccount' => true,
        ];
    }
}
