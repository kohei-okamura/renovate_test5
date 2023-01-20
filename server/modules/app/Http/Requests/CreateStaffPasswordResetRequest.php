<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidatesWhenResolved;

/**
 * スタッフパスワード再設定作成リクエスト.
 *
 * @property-read string $email
 */
class CreateStaffPasswordResetRequest extends OrganizationRequest implements ValidatesWhenResolved
{
    use FormRequest;

    /** {@inheritdoc} */
    protected function rules(array $input): array
    {
        return [
            'email' => ['required', 'email', 'max:255'],
        ];
    }
}
