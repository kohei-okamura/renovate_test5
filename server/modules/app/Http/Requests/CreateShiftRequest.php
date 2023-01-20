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
use Domain\Shift\Shift;
use Domain\Shift\Task;
use Illuminate\Contracts\Validation\ValidatesWhenResolved;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use ScalikePHP\Seq;

/**
 * 勤務シフト登録リクエスト.
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
class CreateShiftRequest extends StaffRequest implements ValidatesWhenResolved
{
    use FormRequest;

    /**
     * 勤務シフトを生成する.
     *
     * @return \Domain\Shift\Shift
     */
    public function payload(): Shift
    {
        $assignees = Seq::fromArray($this->assignees)
            ->map(function (array $assignee) {
                $isUndecided = isset($assignee['isUndecided']) ? (bool)$assignee['isUndecided'] : false;
                return Assignee::create([
                    'staffId' => $isUndecided
                        ? null : ($assignee['staffId'] ?? null),
                    'isUndecided' => $isUndecided,
                    'isTraining' => isset($assignee['isTraining']) ? (bool)$assignee['isTraining'] : false,
                ]);
            })
            ->toArray();
        $durations = Seq::fromArray($this->durations)
            ->map(fn (array $duration): Duration => Duration::create([
                'activity' => Activity::from($duration['activity']),
                'duration' => $duration['duration'],
            ]))
            ->toArray();
        $options = $this->options
            ? Seq::fromArray($this->options)
                ->map(fn ($option): ServiceOption => ServiceOption::from($option))
                ->toArray()
            : [];
        $start = Carbon::create($this->schedule['date'] . ' ' . $this->schedule['start']);
        $end = Carbon::create($this->schedule['date'] . ' ' . $this->schedule['end']);
        return Shift::create([
            'task' => Task::from($this->task),
            'serviceCode' => $this->serviceCode !== null ? ServiceCode::fromString($this->serviceCode) : null,
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
            'note' => $this->note ?? '',
            'isCanceled' => false,
            'isConfirmed' => false,
            'reason' => '',
            'createdAt' => Carbon::now(),
            'updatedAt' => Carbon::now(),
        ]);
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
                'user_exists:' . Permission::createShifts(),
                'user_belongs_to_office:officeId,task,' . Permission::createShifts(),
            ],
            'officeId' => [
                'bail',
                'required',
                'office_exists:' . Permission::createShifts(),
            ],
            'assignerId' => [
                'bail',
                'required',
                'staff_exists:' . Permission::createShifts(),
                'staff_belongs_to_office:officeId,' . Permission::createShifts(),
            ],
            'assignees' => ['required', 'array'],
            'assignees.*.staffId' => [
                'bail',
                'integer',
                'distinct',
                'staff_exists:' . Permission::createShifts(),
                'staff_belongs_to_office:officeId,' . Permission::createShifts(),
            ],
            'headcount' => ['bail', 'required', 'integer', 'between:1,2', 'equal_to_length_of:assignees'],
            'schedule.start' => ['required', 'date_format:H:i'],
            'schedule.end' => ['required', 'bail', 'date_format:H:i'],
            'schedule.date' => ['required', 'date', 'after_or_equal:today'],
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
