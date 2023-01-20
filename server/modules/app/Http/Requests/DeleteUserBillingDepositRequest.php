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
 * 利用者請求入金日削除リクエスト.
 *
 * @property-read array $ids
 */
class DeleteUserBillingDepositRequest extends StaffRequest implements ValidatesWhenResolved
{
    use FormRequest;

    /** {@inheritdoc} */
    protected function rules(array $input): array
    {
        return [
            'ids' => [
                'required',
                'array',
                'user_billing_exists:' . Permission::updateUserBillings(),
                'user_billing_deposit_can_delete',
            ],
        ];
    }
}
