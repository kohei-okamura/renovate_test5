<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\BankAccount;

use Domain\Entity;

/**
 * 銀行口座.
 *
 * @property-read string $bankName 銀行名
 * @property-read string $bankCode 銀行コード
 * @property-read string $bankBranchName 銀行支店名
 * @property-read string $bankBranchCode 銀行支店コード
 * @property-read \Domain\BankAccount\BankAccountType $bankAccountType 銀行口座種別
 * @property-read string $bankAccountNumber 銀行口座番号
 * @property-read string $bankAccountHolder 銀行口座名義
 * @property-read int $version バージョン
 * @property-read \Domain\Common\Carbon $updatedAt
 * @property-read \Domain\Common\Carbon $createdAt
 */
final class BankAccount extends Entity
{
    /** ゆうちょ銀行の銀行コード */
    public const JAPAN_POST_BANK_BANK_CODE = '9900';

    /**
     * 銀行口座番号が正常かどうかを返す.
     *
     * @param string $bankCode
     * @param string $bankAccountNumber
     * @return bool
     */
    public static function isValidBankAccountNumber(string $bankCode, string $bankAccountNumber): bool
    {
        return $bankCode === self::JAPAN_POST_BANK_BANK_CODE
            // ゆうちょ銀行の場合、8 桁、かつ末尾が 1
            ? strlen($bankAccountNumber) === 8 && str_ends_with($bankAccountNumber, '1')
            // ゆうちょ銀行以外の場合、7 桁
            : strlen($bankAccountNumber) === 7;
    }

    /** {@inheritdoc} */
    protected function attrs(): array
    {
        return [
            ...parent::attrs(),
            'bankName',
            'bankCode',
            'bankBranchName',
            'bankBranchCode',
            'bankAccountType',
            'bankAccountNumber',
            'bankAccountHolder',
            'version',
            'createdAt',
            'updatedAt',
        ];
    }

    /** {@inheritdoc} */
    protected function jsonables(): array
    {
        return [
            'id' => true,
            'bankName' => true,
            'bankCode' => true,
            'bankBranchName' => true,
            'bankBranchCode' => true,
            'bankAccountType' => true,
            'bankAccountNumber' => true,
            'bankAccountHolder' => true,
            'version' => false,
            'createdAt' => true,
            'updatedAt' => true,
        ];
    }
}
