<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Http\Requests;

use Domain\Common\Carbon;
use Domain\Office\OfficeGroup;
use Illuminate\Contracts\Validation\ValidatesWhenResolved;

/**
 * 事業所グループ作成リクエスト.
 *
 * @property-read int $parentOfficeGroupId
 * @property-read string $name
 */
class CreateOfficeGroupRequest extends StaffRequest implements ValidatesWhenResolved
{
    use FormRequest;

    /**
     * 事業所グループを生成する.
     *
     * @return \Domain\Office\OfficeGroup
     */
    public function payload(): OfficeGroup
    {
        return OfficeGroup::create([
            'parentOfficeGroupId' => $this->parentOfficeGroupId,
            'name' => $this->name,
            'createdAt' => Carbon::now(),
            'updatedAt' => Carbon::now(),
        ]);
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
