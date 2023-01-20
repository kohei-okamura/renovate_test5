<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Http\Requests;

use App\Http\Requests\FormRequest;
use App\Http\Requests\OrganizationRequest;

/**
 * テスト用 FormRequest.
 */
class FormRequestFixture extends OrganizationRequest
{
    use FormRequest;

    /** {@inheritdoc} */
    protected function attributes(): array
    {
        return [
            'input_key' => '入力値',
        ];
    }

    /** {@inheritdoc} */
    protected function messages(): array
    {
        return [
            'input_key.required' => 'xxx:attribute が入力されていません。xxx',
        ];
    }

    /** {@inheritdoc} */
    protected function rules(array $input): array
    {
        return [
            'input_key' => ['required', 'email'],
        ];
    }
}
