<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Http\Requests;

use Domain\Common\Carbon;
use Domain\Permission\Permission;
use Illuminate\Contracts\Validation\ValidatesWhenResolved;

/**
 * 利用者請求：領収書作成リクエスト.
 *
 * @property-read array&int[] $ids
 * @property-read \Domain\Common\Carbon $issuedOn
 */
class CreateUserBillingReceiptRequest extends StaffRequest implements ValidatesWhenResolved
{
    use FormRequest;

    /**
     * 領収書作成用の配列を生成する.
     *
     * @return array
     */
    public function payload(): array
    {
        return [
            'ids' => $this->ids,
            'issuedOn' => Carbon::parse($this->issuedOn),
        ];
    }

    /** {@inheritdoc} */
    protected function rules(array $input): array
    {
        return [
            'ids' => [
                'required',
                'array',
                'user_billing_exists:' . Permission::viewUserBillings(),
                'user_billing_result_is_not_none',
                'user_billing_deposit_required:' . Permission::viewUserBillings(),
            ],
            'issuedOn' => ['required', 'date'],
        ];
    }
}
