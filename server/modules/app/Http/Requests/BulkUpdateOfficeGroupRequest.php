<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidatesWhenResolved;
use ScalikePHP\Seq;

/**
 * 事業所グループ一括更新リクエスト.
 *
 * @property-read array $list
 */
class BulkUpdateOfficeGroupRequest extends StaffRequest implements ValidatesWhenResolved
{
    use FormRequest;

    /**
     * 一括更新用の事業所グループ情報が入っている配列を生成する.
     *
     * @return array 配列の配列. 中身は事業所グループ情報.
     */
    public function payload(): array
    {
        return Seq::fromArray($this->list)
            ->map(function (array $input): array {
                return [
                    'id' => $input['id'],
                    'parentOfficeGroupId' => $input['parentOfficeGroupId'] ?? null,
                    'sortOrder' => $input['sortOrder'],
                ];
            })
            ->toArray();
    }

    /** {@inheritdoc} */
    protected function rules(array $input): array
    {
        return [
            'list' => ['required', 'array'],
            'list.*.id' => ['required', 'office_group_exists'],
            'list.*.parentOfficeGroupId' => ['nullable', 'office_group_exists'],
            'list.*.sortOrder' => ['required', 'integer'],
        ];
    }
}
