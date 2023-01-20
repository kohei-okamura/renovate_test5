<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 *  UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Http\Requests;

use Domain\BankAccount\BankAccountType;
use Illuminate\Contracts\Validation\ValidatesWhenResolved;

/**
 * スタッフ銀行口座更新リクエスト.
 *
 * @property-read string $bankName
 * @property-read string $bankCode
 * ß@property-read string $bankBranchName
 * @property-read string $bankBranchCode
 * @property-read int $bankAccountType
 * @property-read string $bankAccountNumber
 * @property-read string $bankAccountHolder
 */
class UpdateStaffBankAccountRequest extends StaffRequest implements ValidatesWhenResolved
{
    use FormRequest;

    /**
     * 更新用の配列を生成する。
     *
     * @return array
     */
    public function payload(): array
    {
        return [
            'bankName' => $this->bankName,
            'bankCode' => $this->bankCode,
            'bankBranchName' => $this->bankBranchName,
            'bankBranchCode' => $this->bankBranchCode,
            'bankAccountType' => BankAccountType::from($this->bankAccountType),
            'bankAccountNumber' => $this->bankAccountNumber,
            'bankAccountHolder' => $this->bankAccountHolder,
        ];
    }

    /** {@inheritdoc} */
    protected function rules(array $input): array
    {
        return [
            'bankName' => ['required', 'max:100'],
            'bankCode' => ['required', 'digits:4'],
            'bankBranchName' => ['required', 'max:100'],
            'bankBranchCode' => ['required', 'digits:3'],
            'bankAccountType' => ['required', 'bank_account_type'],
            'bankAccountNumber' => ['required', 'digits:7'],
            'bankAccountHolder' => ['required', 'max:100', 'zengin_data_record_char'],
        ];
    }
}
