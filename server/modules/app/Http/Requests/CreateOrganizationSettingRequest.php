<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Http\Requests;

use Domain\Organization\OrganizationSetting;
use Illuminate\Contracts\Validation\ValidatesWhenResolved;

/**
 * 事業者別設定作成リクエスト.
 *
 * @property-read string $bankingClientCode
 */
class CreateOrganizationSettingRequest extends StaffRequest implements ValidatesWhenResolved
{
    use FormRequest;

    /**
     * 事業者別設定を生成する.
     */
    public function payload(): OrganizationSetting
    {
        $values = ['bankingClientCode' => $this->bankingClientCode];
        return OrganizationSetting::create($values);
    }

    /** {@inheritdoc} */
    protected function rules(array $input): array
    {
        return [
            'bankingClientCode' => ['digits:10'],
        ];
    }
}
