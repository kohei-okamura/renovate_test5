<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Http\Requests;

use Domain\Common\Carbon;
use Domain\Common\Schedule;
use Domain\Permission\Permission;
use Domain\ServiceCode\ServiceCode;
use Domain\Shift\Activity;
use Domain\Shift\Assignee;
use Domain\Shift\Duration;
use Domain\Shift\ServiceOption;
use Domain\Shift\Task;
use Illuminate\Contracts\Validation\ValidatesWhenResolved;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;

/**
 * 勤務実績編集リクエスト.
 *
 * @property-read int $task
 * @property-read string $serviceCode
 * @property-read int $userId
 * @property-read int $officeId
 * @property-read int $contractId
 * @property-read int $assignerId
 * @property-read array $assignees
 * @property-read int $headcount
 * @property-read array $schedule
 * @property-read array $durations
 * @property-read array $options
 * @property-read string $note
 * @property-read bool $isConfirmed
 */
class UpdateAttendanceRequest extends StaffRequest implements ValidatesWhenResolved
{
    use FormRequest;

    /**
     * リクエストを勤務実績に変換する.
     *
     * @return array
     */
    public function payload(): array
    {
        $assignees = array_map(
            fn (array $assignee, int $index) => Assignee::create([
                'sort_order' => $index,
                'staffId' => $assignee['staffId'] ?? null,
                'isUndecided' => $assignee['isUndecided'] ?? false,
                'isTraining' => $assignee['isTraining'] ?? false,
            ]),
            $this->assignees,
            array_keys($this->assignees)
        );
        $durations = array_map(
            fn (array $duration): Duration => Duration::create([
                'activity' => Activity::from($duration['activity']),
                'duration' => $duration['duration'],
            ]),
            $this->durations
        );
        $options = array_map(fn ($option) => ServiceOption::from($option), $this->options);
        $start = Carbon::create($this->schedule['date'] . ' ' . $this->schedule['start']);
        $end = Carbon::create($this->schedule['date'] . ' ' . $this->schedule['end']);
        return [
            'task' => Task::from($this->task),
            'serviceCode' => $this->serviceCode ? ServiceCode::fromString($this->serviceCode) : null,
            'userId' => $this->userId,
            'officeId' => $this->officeId,
            'assignerId' => $this->assignerId,
            'assignees' => $assignees,
            'headcount' => $this->headcount,
            'schedule' => Schedule::create([
                'start' => $start,
                'end' => $end > $start ? $end : $end->addDay(),
                'date' => Carbon::create($this->schedule['date']),
            ]),
            'durations' => $durations,
            'options' => $options,
            'note' => $this->note,
            'updatedAt' => Carbon::now(),
        ];
    }

    /** {@inheritdoc} */
    protected function rules(array $input): array
    {
        return [
            'task' => ['required', 'task'],
            'serviceCode' => ['string', 'max:6', 'regex:/[A-Z0-9]/'],
            'userId' => [
                'bail',
                Rule::requiredIf(function () use ($input): bool {
                    $value = Arr::get($input, 'task');
                    return Task::isValid($value) && Task::from($value)->toServiceSegment()->nonEmpty();
                }),
                'user_exists:' . Permission::updateAttendances(),
                'user_belongs_to_office:officeId,task,' . Permission::updateAttendances(),
            ],
            'officeId' => [
                'required',
                'office_exists:' . Permission::updateAttendances(),
            ],
            'assignerId' => [
                'bail',
                'required',
                'staff_exists:' . Permission::updateAttendances(),
                'staff_belongs_to_office:officeId,' . Permission::updateAttendances(),
            ],
            'assignees' => ['required', 'array'],
            'assignees.*.staffId' => [
                'bail',
                'integer',
                'distinct',
                'staff_exists:' . Permission::updateAttendances(),
                'staff_belongs_to_office:officeId,' . Permission::updateAttendances(),
            ],
            'headcount' => ['bail', 'required', 'integer', 'between:1,2', 'equal_to_length_of:assignees'],
            'schedule.start' => ['required', 'date_format:H:i'],
            'schedule.end' => ['required', 'bail', 'date_format:H:i'],
            'schedule.date' => ['required', 'date', 'before:' . Carbon::tomorrow()->toDateString()],
            'durations' => [
                'bail',
                'required',
                'array',
                'durations_equal_to_schedule:schedule.start,schedule.end',
                'have_integrity_of:task',
            ],
            'durations.*.activity' => ['required', 'activity'],
            'durations.*.duration' => ['bail', 'required', 'integer', 'min:0'],
            'options' => ['array'],
            'options.*' => ['service_option', 'shift_attendance_service_option:task'],
            'note' => ['string'],
        ];
    }
}
