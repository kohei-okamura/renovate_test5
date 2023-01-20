<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidatesWhenResolved;

/**
 * 事業所グループ更新リクエスト.
 *
 * @property-read int $parentOfficeGroupId
 * @property-read string $name
 * @property-read int $sortOrder
 */
class UpdateOfficeGroupRequest extends StaffRequest implements ValidatesWhenResolved
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
            'parentOfficeGroupId' => $this->parentOfficeGroupId,
            'name' => $this->name,
        ];
    }

    /** {@inheritdoc} */
    protected function rules(array $input): array
    {
        return [
            'parentOfficeGroupId' => ['nullable', 'office_group_exists'],
            'name' => ['required', 'max:100'],
        ];
    }
}
