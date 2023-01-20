<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Http\Requests;

use Domain\Permission\Permission;
use Illuminate\Contracts\Validation\ValidatesWhenResolved;

/**
 * 勤務シフト確定リクエスト.
 *
 * @property-read array $ids
 */
class ConfirmShiftRequest extends StaffRequest implements ValidatesWhenResolved
{
    use FormRequest;

    /** {@inheritdoc} */
    protected function rules(array $input): array
    {
        return [
            'ids' => ['required', 'array', 'shift_exists:' . Permission::updateShifts()],
        ];
    }
}
