<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Http\Requests;

use Domain\Permission\Permission;
use Illuminate\Contracts\Validation\ValidatesWhenResolved;
use UseCase\Shift\LookupAttendanceUseCase;

/**
 * 勤務実績キャンセルリクエスト.
 *
 * @property-read int $id
 * @property-read string $reason
 */
class CancelAttendanceRequest extends StaffRequest implements ValidatesWhenResolved
{
    use FormRequest;

    /** {@inheritdoc} */
    protected function rules(array $input): array
    {
        return [
            'id' => ['non_canceled:' . LookupAttendanceUseCase::class . ',' . Permission::updateAttendances()],
            'reason' => ['required', 'string', 'max:255'],
        ];
    }
}
