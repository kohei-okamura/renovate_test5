<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Http\Requests;

use Domain\Permission\Permission;
use Illuminate\Contracts\Validation\ValidatesWhenResolved;

/**
 * 口座振替データ作成リクエスト.
 *
 * @property-read array $userBillingIds
 */
class CreateWithdrawalTransactionRequest extends StaffRequest implements ValidatesWhenResolved
{
    use FormRequest;

    /** {@inheritdoc} */
    protected function rules(array $input): array
    {
        return [
            'userBillingIds' => [
                'required',
                'array',
                'user_billing_exists:' . Permission::createWithdrawalTransactions(),
                'all_payment_methods_are_withdrawal:' . Permission::createWithdrawalTransactions(),
                'all_user_billings_are_pending:' . Permission::createWithdrawalTransactions(),
                'user_billing_bank_account_number_digits:' . Permission::createWithdrawalTransactions(),
            ],
        ];
    }
}
