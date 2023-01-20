<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Http\Requests;

use Domain\BankAccount\BankAccountType;
use Domain\User\PaymentMethod;
use Domain\UserBilling\UserBillingBankAccount;
use Illuminate\Contracts\Validation\ValidatesWhenResolved;

/**
 * 利用者請求更新リクエスト.
 *
 * @property-read int $carriedOverAmount
 * @property-read int $paymentMethod
 * @property-read array $bankAccount
 */
class UpdateUserBillingRequest extends StaffRequest implements ValidatesWhenResolved
{
    use FormRequest;

    /**
     * リクエストを利用者請求更新用の配列に変換する.
     *
     * @return array
     */
    public function payload(): array
    {
        return [
            'carriedOverAmount' => $this->carriedOverAmount,
            'paymentMethod' => PaymentMethod::from($this->paymentMethod),
            'bankAccount' => UserBillingBankAccount::create([
                ...$this->bankAccount,
                'bankAccountType' => BankAccountType::from($this->bankAccount['bankAccountType']),
            ]),
        ];
    }

    /** {@inheritdoc} */
    protected function rules(array $input): array
    {
        $paymentMethod = PaymentMethod::withdrawal()->value();
        return [
            'id' => ['required', 'user_billing_can_update'],
            'carriedOverAmount' => ['required', 'integer', 'user_billing_amount_is_non_negative'],
            'paymentMethod' => ['required', 'payment_method', 'user_billing_changeable_payment_method'],
            'bankAccount.bankName' => ["required_if:paymentMethod,{$paymentMethod}", 'max:100'],
            'bankAccount.bankCode' => ["required_if:paymentMethod,{$paymentMethod}", 'digits:4'],
            'bankAccount.bankBranchName' => ["required_if:paymentMethod,{$paymentMethod}", 'max:100'],
            'bankAccount.bankBranchCode' => ["required_if:paymentMethod,{$paymentMethod}", 'digits:3'],
            'bankAccount.bankAccountType' => ["required_if:paymentMethod,{$paymentMethod}", 'bank_account_type'],
            'bankAccount.bankAccountNumber' => ["required_if:paymentMethod,{$paymentMethod}", 'bank_account_number_digits:bankAccount.bankCode'],
            'bankAccount.bankAccountHolder' => ["required_if:paymentMethod,{$paymentMethod}", 'max:100', 'zengin_data_record_char'],
        ];
    }

    /** {@inheritdoc} */
    protected function messages(): array
    {
        return [
            'bankAccount.bankName.required_if' => '入力してください。',
            'bankAccount.bankCode.required_if' => '入力してください。',
            'bankAccount.bankBranchName.required_if' => '入力してください。',
            'bankAccount.bankBranchCode.required_if' => '入力してください。',
            'bankAccount.bankAccountType.required_if' => '入力してください。',
            'bankAccount.bankAccountNumber.required_if' => '入力してください。',
            'bankAccount.bankAccountHolder.required_if' => '入力してください。',
        ];
    }
}
