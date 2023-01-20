<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidatesWhenResolved;

/**
 * 利用者選択肢一覧取得リクエスト.
 *
 * @property-read null|int[] $officeIds
 * @property-read string $permission
 */
class GetIndexUserOptionRequest extends StaffRequest implements ValidatesWhenResolved
{
    use FormRequest;

    /** {@inheritdoc} */
    protected function rules(array $input): array
    {
        return [
            'permission' => ['required', 'permission', 'authorized_permission'],
            'officeIds' => ['nullable', 'office_exists:' . $input['permission']],
        ];
    }
}
