<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidatesWhenResolved;

/**
 * 事業者別設定更新リクエスト.
 *
 * @property-read string $bankingClientCode
 */
class UpdateOrganizationSettingRequest extends StaffRequest implements ValidatesWhenResolved
{
    use FormRequest;

    /**
     * 事業者別設定を生成する.
     */
    public function payload(): array
    {
        return [
            'bankingClientCode' => $this->bankingClientCode,
        ];
    }

    /** {@inheritdoc} */
    protected function rules(array $input): array
    {
        return [
            'bankingClientCode' => ['digits:10'],
        ];
    }
}
