<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Examples;

use Domain\Common\Carbon;
use Domain\Common\Schedule;
use Domain\ServiceCode\ServiceCode;
use Domain\Shift\Assignee;
use Domain\Shift\Duration;
use Domain\Shift\ServiceOption;
use Domain\Shift\Shift;
use Domain\Shift\Task;
use Faker\Generator;
use ScalikePHP\Seq;

/**
 * Shift Examples.
 *
 * @property-read \Domain\Shift\Shift[] $shifts
 * @mixin \Tests\Unit\Examples\OrganizationExample
 * @mixin \Tests\Unit\Examples\StaffExample
 * @mixin \Tests\Unit\Examples\OfficeExample
 * @mixin \Tests\Unit\Examples\UserExample
 * @mixin \Tests\Unit\Examples\ContractExample
 */
trait ShiftExample
{
    /**
     * 勤務シフトの一覧を生成する.
     *
     * @return \Domain\Shift\Shift[]
     *
     * NOTE: [5]と[6]は、ShiftRepositoryのremove系のテストで使用するのでリレーションしない
     */
    protected function shifts(): array
    {
        $faker = app(Generator::class);
        assert($faker instanceof Generator);
        $options2 = $faker->randomElements(ServiceOption::all(), 2, false);
        sort($options2);
        return [
            $this->generateShift([
                'id' => 1,
                'organizationId' => $this->organizations[0]->id,
                'officeId' => $this->offices[0]->id,
                'assignerId' => $this->staffs[10]->id,
                'task' => Task::dwsVisitingCareForPwsd(),
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
                'isCanceled' => false,
                'reason' => 'キャンセル理由',
            ]),
            $this->generateShift([
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
                'isCanceled' => false,
            ]),
            $this->generateShift([
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
                'options' => $options2,
                'isConfirmed' => false,
                'isCanceled' => true,
            ]),
            $this->generateShift([
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
                    ServiceOption::firstTime(),
                ],
                'isConfirmed' => true,
                'isCanceled' => true,
            ]),
            $this->generateShift([
                'id' => 5,
                'organizationId' => $this->organizations[0]->id,
                'task' => Task::ltcsPhysicalCareAndHousework(),
            ]),
            $this->generateShift([
                'id' => 6,
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
                    'start' => Carbon::parse('2040-11-12T10:10:00+0900'),
                    'end' => Carbon::parse('2040-11-12T11:10:00+0900'),
                    'date' => Carbon::parse('2040-01-01'),
                ]),
                'options' => [],
            ]),
            $this->generateShift([
                'id' => 7,
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
                    'start' => Carbon::parse('2040-11-12T11:10:00+0900'),
                    'end' => Carbon::parse('2040-11-12T12:10:00+0900'),
                    'date' => Carbon::parse('2040-04-09'),
                ]),
                'options' => $faker->randomElements(ServiceOption::all(), 1, false),
            ]),
            $this->generateShift([
                'id' => 8,
                'organizationId' => $this->organizations[0]->id,
                'schedule' => Schedule::create([
                    'start' => Carbon::parse('2040-11-12T12:10:00+0900'),
                    'end' => Carbon::parse('2040-11-12T12:40:00+0900'),
                    'date' => Carbon::parse('2040-04-30'),
                ]),
                'isConfirmed' => false,
            ]),
            $this->generateShift([
                'id' => 9,
                'organizationId' => $this->organizations[0]->id,
                'schedule' => Schedule::create([
                    'start' => Carbon::parse('2040-11-12T13:50:00+0900'),
                    'end' => Carbon::parse('2040-11-12T14:50:00+0900'),
                    'date' => Carbon::parse('2040-04-30'),
                ]),
                'isConfirmed' => false,
            ]),
            $this->generateShift([
                'id' => 10,
                'organizationId' => $this->organizations[1]->id,
                'schedule' => Schedule::create([
                    'start' => Carbon::parse('2030-11-12T13:50:00+0900'),
                    'end' => Carbon::parse('2030-11-12T14:50:00+0900'),
                    'date' => Carbon::parse('2030-11-12'),
                ]),
                'options' => [ServiceOption::notificationEnabled()],
                'isConfirmed' => false,
                'isCanceled' => false,
            ]),
            $this->generateShift([
                'id' => 11,
                'organizationId' => $this->organizations[0]->id,
                'officeId' => $this->offices[2]->id,
                'isConfirmed' => false,
            ]),
            $this->generateShift([
                'id' => 12,
                'organizationId' => $this->organizations[0]->id,
                'officeId' => $this->offices[0]->id,
                'assignerId' => $this->staffs[10]->id,
                'task' => Task::dwsVisitingCareForPwsd(),
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
                    'start' => Carbon::create(2019, 1, 1),
                    'end' => Carbon::create(2019, 1, 1)->addHour(),
                    'date' => Carbon::create(2019, 1, 1),
                ]),
                'options' => [],
                'isCanceled' => false,
                'reason' => 'キャンセル理由',
            ]),
        ];
    }

    /**
     * Generate an example of Shift.
     *
     * @param array $overwrites
     * @return \Domain\Shift\Shift
     */
    private function generateShift(array $overwrites): Shift
    {
        $faker = app(Generator::class);
        assert($faker instanceof Generator);
        $headcount = 1;
        $f = function () use ($faker) {
            yield Assignee::create([
                'staffId' => $this->staffs[10]->id,
                'isUndecided' => false,
                'isTraining' => $faker->boolean(),
            ]);
        };
        $minute = $faker->numberBetween(15, 300) * 2;
        $start = Carbon::today()->addDays($faker->numberBetween(0, 10000));
        $task = $overwrites['task'] ?? $faker->randomElement(Task::all());
        $values = [
            'contractId' => $this->contracts[2]->id,
            'officeId' => $this->offices[0]->id,
            'userId' => $this->users[0]->id,
            'assignerId' => $this->staffs[8]->id,
            'task' => $task,
            'serviceCode' => ServiceCode::fromString('123456'),
            'headcount' => $headcount,
            'assignees' => Seq::fromArray($f())->take($headcount)->toArray(),
            'schedule' => Schedule::create([
                'start' => $start,
                'end' => $start->addMinutes($minute),
                'date' => $start->startOfDay(),
            ]),
            'durations' => [
                Duration::create([
                    'activity' => $faker->randomElement($task->toActivities()),
                    'duration' => $minute / 2,
                ]),
                Duration::create([
                    'activity' => $faker->randomElement($task->toActivities()),
                    'duration' => $minute / 2,
                ]),
            ],
            'options' => [],
            'note' => $faker->realText(100),
            'isConfirmed' => $faker->boolean(),
            'isCanceled' => $faker->boolean(),
            'reason' => '',
            'createdAt' => Carbon::instance($faker->dateTime),
            'updatedAt' => Carbon::instance($faker->dateTime),
        ];
        return Shift::create($overwrites + $values);
    }
}
