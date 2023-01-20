<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Http\Requests;

use Domain\Permission\Permission;
use Domain\Shift\Task;

/**
 * 勤務シフト検索リクエスト.
 */
class FindShiftRequest extends FindRequest
{
    /** {@inheritdoc} */
    protected function boolParams(): array
    {
        return ['isConfirmed'];
    }

    /** {@inheritdoc} */
    protected function carbonParams(): array
    {
        return [
            'start',
            'end',
        ];
    }

    /** {@inheritdoc} */
    protected function enumParams(): array
    {
        return ['task' => Task::class];
    }

    /** {@inheritdoc} */
    protected function filterKeys(): array
    {
        return [
            'userId',
            'assigneeId',
            'assignerId',
            'officeId',
            'task',
            'isConfirmed',
            'start',
            'end',
        ];
    }

    /** {@inheritdoc} */
    protected function rules(array $input): array
    {
        return [
            ...parent::rules($input),
            'userId' => ['nullable', 'user_exists:' . Permission::listShifts()],
            'assigneeId' => ['nullable', 'staff_exists:' . Permission::listShifts()],
            'assignerId' => ['nullable', 'staff_exists:' . Permission::listShifts()],
            'officeId' => ['nullable', 'office_exists:' . Permission::listShifts()],
            'task' => ['nullable', 'task'],
            'isConfirmed' => ['boolean_ext'],
            'start' => ['nullable', 'date'],
            'end' => ['bail', 'nullable', 'date', 'after_or_equal:start'],
        ];
    }

    /** {@inheritdoc} */
    protected function attributes(): array
    {
        return [
            'start' => '勤務日（開始）',
        ];
    }
}
