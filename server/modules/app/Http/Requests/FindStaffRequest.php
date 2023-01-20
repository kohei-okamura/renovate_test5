<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Http\Requests;

use Domain\Permission\Permission;
use Domain\Staff\StaffStatus;

/**
 * スタッフ検索リクエスト.
 */
class FindStaffRequest extends FindRequest
{
    /** {@inheritdoc} */
    protected function filterKeys(): array
    {
        return ['officeId', 'q', 'status'];
    }

    /** {@inheritdoc} */
    protected function enumParams(): array
    {
        return ['status' => StaffStatus::class];
    }

    /** {@inheritdoc} */
    protected function rules(array $input): array
    {
        return [
            ...parent::rules($input),
            'officeId' => ['nullable', 'office_exists:' . Permission::listStaffs()],
            'status' => ['nullable', 'array'],
            'status.*' => ['required', 'staff_status'],
        ];
    }
}
