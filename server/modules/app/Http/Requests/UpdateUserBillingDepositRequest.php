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
 * 利用者請求入金日更新リクエスト.
 *
 * @property-read int[] $ids
 * @property-read string $depositedOn
 */
class UpdateUserBillingDepositRequest extends StaffRequest implements ValidatesWhenResolved
{
    use FormRequest;

    /**
     * 更新用の配列を生成する.
     *
     * @return array
     */
    public function payload(): array
    {
        return [
            'ids' => $this->ids,
            'depositedAt' => Carbon::parse($this->depositedOn),
        ];
    }

    /** {@inheritdoc} */
    protected function rules(array $input): array
    {
        return [
            'ids' => ['bail', 'required', 'array', 'user_billing_deposit_can_update:' . Permission::updateUserBillings()],
            'depositedOn' => ['bail', 'required', 'date', 'before:' . Carbon::tomorrow()->toDateString()],
        ];
    }
}
