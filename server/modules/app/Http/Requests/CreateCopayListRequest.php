<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidatesWhenResolved;

/**
 * 利用者負担額一覧表リクエスト.
 *
 * @property-read array|int[] $ids
 * @property-read bool $isDivided
 */
class CreateCopayListRequest extends StaffRequest implements ValidatesWhenResolved
{
    use FormRequest;

    /** {@inheritdoc} */
    protected function rules(array $input): array
    {
        return [
            'ids' => ['required', 'array', 'copay_list_can_download'],
            'isDivided' => ['required', 'boolean_ext'],
        ];
    }
}
