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
 * 介護サービス利用明細書作成リクエスト
 *
 * @property-read array|int[] $ids
 * @property \Domain\Common\Carbon $issuedOn
 */
class CreateUserBillingStatementRequest extends StaffRequest implements ValidatesWhenResolved
{
    use FormRequest;

    /**
     * 介護サービス利用明細書作成用の配列を生成する.
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
                'user_billing_has_other_than_own_expense_service:' . Permission::viewUserBillings(),
            ],
            'issuedOn' => ['required', 'date'],
        ];
    }
}
