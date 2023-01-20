<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Examples;

use Domain\Common\Carbon;
use Domain\Common\Schedule;
use Domain\ServiceCode\ServiceCode;
use Domain\Shift\Activity;
use Domain\Shift\Assignee;
use Domain\Shift\Attendance;
use Domain\Shift\Duration;
use Domain\Shift\ServiceOption;
use Domain\Shift\Task;
use Faker\Generator;
use ScalikePHP\Seq;

/**
 * Attendance Examples.
 *
 * @property-read Attendance[] $attendances
 * @mixin \Tests\Unit\Examples\OrganizationExample
 * @mixin \Tests\Unit\Examples\StaffExample
 * @mixin \Tests\Unit\Examples\OfficeExample
 * @mixin \Tests\Unit\Examples\UserExample
 * @mixin \Tests\Unit\Examples\ContractExample
 */
trait AttendanceExample
{
    /**
     * 勤務実績の一覧を生成する.
     *
     * @return \Domain\Shift\Attendance[]
     */
    protected function attendances(): array
    {
        $faker = app(Generator::class);
        assert($faker instanceof Generator);
        $days = $faker->numberBetween(0, 100);
        $start = Carbon::now()->addDays($days)->setMicro(0);
        $duration = $faker->numberBetween(1, 60);
        return [
            $this->generateAttendance([
                'id' => 1,
                'organizationId' => $this->organizations[0]->id,
                'officeId' => $this->offices[0]->id,
                'assignerId' => $this->staffs[10]->id,
                'task' => Task::assessment(),
                'headcount' => 2,
                'assignees' => [
                    Assignee::create([
                        'staffId' => $this->staffs[10]->id,
                        'isUndecided' => false,
                        'isTraining' => $faker->boolean(),
                    ]),
                    Assignee::create([
                        'staffId' => $this->staffs[11]->id,
                        'isUndecided' => false,
                        'isTraining' => $faker->boolean(),
                    ]),
                ],
                'isConfirmed' => false,
                'schedule' => Schedule::create([
                    'start' => Carbon::parse('2040-11-12T10:00:00+0900'),
                    'end' => Carbon::parse('2040-11-12T11:00:00+0900'),
                    'date' => Carbon::parse('2040-01-01'),
                ]),
                'options' => [],
            ]),
            $this->generateAttendance([
                'id' => 2,
                'organizationId' => $this->organizations[0]->id,
                'officeId' => $this->offices[0]->id,
                'assignerId' => $this->staffs[10]->id,
                'task' => Task::commAccompany(),
                'headcount' => 1,
                'assignees' => [
                    Assignee::create([
                        'staffId' => $this->staffs[13]->id,
                        'isUndecided' => false,
                        'isTraining' => $faker->boolean(),
                    ]),
                ],
                'isConfirmed' => true,
                'schedule' => Schedule::create([
                    'start' => Carbon::parse('2040-11-12T11:00:00+0900'),
                    'end' => Carbon::parse('2040-11-12T12:00:00+0900'),
                    'date' => Carbon::parse('2040-04-09'),
                ]),
                'options' => $faker->randomElements(ServiceOption::all(), 1, false),
            ]),
            $this->generateAttendance([
                'id' => 3,
                'organizationId' => $this->organizations[1]->id,
                'officeId' => $this->offices[2]->id,
                'assignerId' => $this->staffs[12]->id,
                'headcount' => 1,
                'assignees' => [
                    Assignee::create([
                        'staffId' => $this->staffs[14]->id,
                        'isUndecided' => false,
                        'isTraining' => $faker->boolean(),
                    ]),
                ],
                'schedule' => Schedule::create([
                    'start' => Carbon::parse('2040-11-12T12:00:00+0900'),
                    'end' => Carbon::parse('2040-11-12T13:00:00+0900'),
                    'date' => Carbon::parse('2040-04-10'),
                ]),
                'options' => $faker->randomElements(ServiceOption::all(), 2, false),
            ]),
            $this->generateAttendance([
                'id' => 4,
                'organizationId' => $this->organizations[0]->id,
                'officeId' => $this->offices[3]->id,
                'assignerId' => $this->staffs[13]->id,
                'headcount' => 2,
                'assignees' => [
                    Assignee::create([
                        'staffId' => $this->staffs[15]->id,
                        'isUndecided' => false,
                        'isTraining' => $faker->boolean(),
                    ]),
                    Assignee::create([
                        'staffId' => $this->staffs[16]->id,
                        'isUndecided' => false,
                        'isTraining' => $faker->boolean(),
                    ]),
                ],
                'userId' => $this->users[1]->id,
                'schedule' => Schedule::create([
                    'start' => Carbon::parse('2040-11-12T13:00:00+0900'),
                    'end' => Carbon::parse('2040-11-12T14:00:00+0900'),
                    'date' => Carbon::parse('2040-04-30'),
                ]),
                'options' => [
                    ServiceOption::notificationEnabled(),
                    ServiceOption::oneOff(),
                ],
            ]),
            $this->generateAttendance([
                'id' => 5,
                'organizationId' => $this->organizations[0]->id,
                'officeId' => $this->offices[0]->id,
                'userId' => $this->users[0]->id,
                'assignerId' => $this->staffs[8]->id,
                'headcount' => 1,
                'task' => Task::ltcsPhysicalCareAndHousework(),
                'schedule' => Schedule::create([
                    'start' => Carbon::today()->setMicro(0),
                    'end' => Carbon::today()->addMinutes($duration * 2),
                    'date' => Carbon::today(),
                ]),
                'durations' => [
                    Duration::create([
                        'activity' => Activity::ltcsPhysicalCare(),
                        'duration' => $duration,
                    ]),
                    Duration::create([
                        'activity' => Activity::ltcsHousework(),
                        'duration' => $duration,
                    ]),
                ],
                'options' => [
                    ServiceOption::notificationEnabled(),
                ],
            ]),
            $this->generateAttendance([
                'id' => 6,
                'organizationId' => $this->organizations[0]->id,
                'officeId' => $this->offices[0]->id,
                'assignerId' => $this->staffs[8]->id,
                'headcount' => 1,
                'task' => Task::ltcsPhysicalCareAndHousework(),
                'schedule' => Schedule::create([
                    'start' => $start,
                    'end' => $start->addMinutes($duration * 2),
                    'date' => $start->startOfDay(),
                ]),
                'durations' => [
                    Duration::create([
                        'activity' => Activity::ltcsPhysicalCare(),
                        'duration' => $duration,
                    ]),
                    Duration::create([
                        'activity' => Activity::ltcsHousework(),
                        'duration' => $duration,
                    ]),
                ],
                'options' => [
                    ServiceOption::notificationEnabled(),
                ],
                'isConfirmed' => true,
            ]),
            $this->generateAttendance([
                'id' => 7,
                'organizationId' => $this->organizations[0]->id,
                'officeId' => $this->offices[2]->id,
                'isConfirmed' => false,
                'isCanceled' => true,
            ]),
            $this->generateAttendance([
                'id' => 8,
                'organizationId' => $this->organizations[1]->id,
                'officeId' => $this->offices[1]->id,
                'isConfirmed' => false,
            ]),
        ];
    }

    /**
     * Generate an example of Attendance.
     *
     * @param array $overwrites
     * @return \Domain\Shift\Attendance
     */
    private function generateAttendance(array $overwrites): Attendance
    {
        $faker = app(Generator::class);
        assert($faker instanceof Generator);
        $headcount = $faker->numberBetween(1, 2);
        $f = function () use ($faker) {
            yield Assignee::create([
                'staffId' => $this->staffs[10]->id,
                'isUndecided' => false,
                'isTraining' => $faker->boolean(),
            ]);
        };
        $activities = $faker->randomElements(Activity::all(), 2);
        sort($activities);
        $values = [
            'contractId' => $this->contracts[2]->id,
            'officeId' => $this->offices[0]->id,
            'userId' => $this->users[0]->id,
            'assignerId' => $this->staffs[8]->id,
            'task' => $faker->randomElement(Task::all()),
            'serviceCode' => ServiceCode::fromString('123456'),
            'headcount' => $headcount,
            'assignees' => Seq::fromArray($f())->take($headcount)->toArray(),
            'schedule' => Schedule::create([
                'start' => Carbon::instance($faker->dateTime()),
                'end' => Carbon::instance($faker->dateTime()),
                'date' => Carbon::instance($faker->dateTimeBetween('now', '+1 month'))->startOfDay(),
            ]),
            'durations' => [
                Duration::create([
                    'activity' => $activities[0],
                    'duration' => $faker->numberBetween(30, 300),
                ]),
                Duration::create([
                    'activity' => $activities[1],
                    'duration' => $faker->numberBetween(301, 600),
                ]),
            ],
            'options' => [],
            'note' => $faker->realText(100),
            'isConfirmed' => $faker->boolean(),
            'isCanceled' => false,
            'reason' => '',
            'createdAt' => Carbon::instance($faker->dateTime),
            'updatedAt' => Carbon::instance($faker->dateTime),
        ];
        return Attendance::create($overwrites + $values);
    }
}
