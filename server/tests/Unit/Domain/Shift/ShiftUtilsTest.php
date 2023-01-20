<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\Shift;

use Domain\Common\Carbon;
use Domain\Common\Schedule;
use Domain\Shift\Activity;
use Domain\Shift\Assignee;
use Domain\Shift\Duration;
use Domain\Shift\ServiceOption;
use Domain\Shift\Shift;
use Domain\Shift\ShiftUtils;
use Domain\Shift\Task;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * ShiftUtils のテスト.
 */
class ShiftUtilsTest extends Test
{
    use CarbonMixin;
    use ContextMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use UnitSupport;

    private const DATE = 51136;
    private const START_MINUTE = 169;
    private const END_MINUTE = 674;
    private const TOTAL_DURATION = 505;
    private const RESTING = 60;
    private const OTHER = 445;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (ShiftUtilsTest $self): void {
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $examples = [
            'when resting is included' => [
                [],
                [],
            ],
            'when resting is not included' => [
                [
                    'durations' => [
                        Duration::create([
                            'activity' => Activity::assessment(),
                            'duration' => 330,
                        ]),
                    ],
                ],
                [
                    'totalDuration' => 330,
                    'resting' => 0,
                ],
            ],
            'when task is dwsVisitingCareForPwsd and outingSupport is included and resting is included' => [
                [
                    'task' => Task::from(Task::dwsVisitingCareForPwsd()->value()),
                    'durations' => [
                        Duration::create([
                            'activity' => Activity::dwsVisitingCareForPwsd(),
                            'duration' => 300,
                        ]),
                        Duration::create([
                            'activity' => Activity::dwsOutingSupportForPwsd(),
                            'duration' => 30,
                        ]),
                        Duration::create([
                            'activity' => Activity::resting(),
                            'duration' => self::RESTING,
                        ]),
                    ],
                ],
                [
                    'task' => Task::dwsVisitingCareForPwsd()->value(),
                    'visitingCare' => 300,
                    'outingSupport' => 30,
                ],
            ],
            'when task is dwsVisitingCareForPwsd and outingSupport is not included and resting is not included' => [
                [
                    'task' => Task::from(Task::dwsVisitingCareForPwsd()->value()),
                    'durations' => [
                        Duration::create([
                            'activity' => Activity::dwsVisitingCareForPwsd(),
                            'duration' => 300,
                        ]),
                    ],
                ],
                [
                    'task' => Task::dwsVisitingCareForPwsd()->value(),
                    'visitingCare' => 300,
                    'resting' => 0,
                ],
            ],
            'when task is ltcsPhysicalCareAndHousework and resting is included' => [
                [
                    'task' => Task::from(Task::ltcsPhysicalCareAndHousework()->value()),
                    'durations' => [
                        Duration::create([
                            'activity' => Activity::ltcsPhysicalCare(),
                            'duration' => 180,
                        ]),
                        Duration::create([
                            'activity' => Activity::ltcsHousework(),
                            'duration' => 160,
                        ]),
                        Duration::create([
                            'activity' => Activity::resting(),
                            'duration' => self::RESTING,
                        ]),
                    ],
                ],
                [
                    'task' => Task::ltcsPhysicalCareAndHousework()->value(),
                    'physicalCare' => 180,
                    'housework' => 160,
                ],
            ],
            'when task is ltcsPhysicalCareAndHousework and resting is not included' => [
                [
                    'task' => Task::from(Task::ltcsPhysicalCareAndHousework()->value()),
                    'durations' => [
                        Duration::create([
                            'activity' => Activity::ltcsPhysicalCare(),
                            'duration' => 180,
                        ]),
                        Duration::create([
                            'activity' => Activity::ltcsHousework(),
                            'duration' => 160,
                        ]),
                    ],
                ],
                [
                    'task' => Task::ltcsPhysicalCareAndHousework()->value(),
                    'physicalCare' => 180,
                    'housework' => 160,
                    'resting' => 0,
                ],
            ],
        ];

        $this->should('convert an array to a domain shift', function ($overwriteExpected, $overwriteInput): void {
            $this->assertModelStrictEquals(
                $this->expectedDomainShift()->copy($overwriteExpected),
                ShiftUtils::fromAssoc(
                    [
                        'officeId' => $this->examples->contracts[1]->officeId,
                        'contractId' => $this->examples->contracts[0]->id,
                    ] + $overwriteInput + $this->shiftDataFromExcel()
                )
            );
        }, compact('examples'));
    }

    /**
     * 期待される勤務シフトドメインモデル.
     *
     * @return \Domain\Shift\Shift
     */
    private function expectedDomainShift(): Shift
    {
        $assignees = [
            Assignee::create([
                'sort_order' => 0,
                'staffId' => $this->examples->shifts[0]->assignees[0]->staffId,
                'isUndecided' => false,
                'isTraining' => true,
            ]),
            Assignee::create([
                'sort_order' => 1,
                'staffId' => $this->examples->shifts[0]->assignees[1]->staffId,
                'isUndecided' => false,
                'isTraining' => false,
            ]),
        ];
        $options = [
            ServiceOption::notificationEnabled(),
            ServiceOption::oneOff(),
            ServiceOption::firstTime(),
            ServiceOption::emergency(),
            ServiceOption::sucking(),
            ServiceOption::welfareSpecialistCooperation(),
            ServiceOption::plannedByNovice(),
            ServiceOption::providedByBeginner(),
            ServiceOption::providedByCareWorkerForPwsd(),
            ServiceOption::over20(),
            ServiceOption::over50(),
            ServiceOption::behavioralDisorderSupportCooperation(),
            ServiceOption::hospitalized(),
            ServiceOption::longHospitalized(),
            ServiceOption::coaching(),
            ServiceOption::vitalFunctionsImprovement1(),
            ServiceOption::vitalFunctionsImprovement2(),
        ];
        $durations = [
            Duration::create([
                'activity' => Activity::assessment(),
                'duration' => self::TOTAL_DURATION - self::RESTING,
            ]),
            Duration::create([
                'activity' => Activity::resting(),
                'duration' => self::RESTING,
            ]),
        ];
        $date = Carbon::parse(Date::excelToDateTimeObject(self::DATE, 'Asia/Tokyo'));
        $start = $date->addMinutes(self::START_MINUTE);
        $end = self::START_MINUTE <= self::END_MINUTE
            ? $date->addMinutes(self::END_MINUTE)
            : $date->addMinutes(self::END_MINUTE)->addDay();
        return Shift::create([
            'task' => Task::from(Task::assessment()->value()),
            'serviceCode' => $this->examples->shifts[0]->serviceCode,
            'userId' => $this->examples->contracts[1]->userId,
            'officeId' => $this->examples->contracts[1]->officeId,
            'contractId' => $this->examples->contracts[0]->id,
            'assignerId' => $this->examples->shifts[0]->assignerId,
            'assignees' => $assignees,
            'headcount' => count($assignees),
            'schedule' => Schedule::create([
                'start' => $start,
                'end' => $end,
                'date' => $date,
            ]),
            'durations' => $durations,
            'options' => $options,
            'note' => '備考',
            'isConfirmed' => false,
            'createdAt' => Carbon::now(),
            'updatedAt' => Carbon::now(),
        ]);
    }

    /**
     * Excel から取得する勤務シフトデータ.
     *
     * @return array
     */
    private function shiftDataFromExcel(): array
    {
        return [
            'isTraining1' => '＊',
            'isTraining2' => null,
            'serviceCode' => $this->examples->shifts[0]->serviceCode->toString(),
            'date' => self::DATE,
            'notificationEnabled' => '＊',
            'oneOff' => '＊',
            'firstTime' => '＊',
            'emergency' => '＊',
            'sucking' => '＊',
            'welfareSpecialistCooperation' => '＊',
            'plannedByNovice' => '＊',
            'providedByBeginner' => '＊',
            'providedByCareWorkerForPwsd' => '＊',
            'over20' => '＊',
            'over50' => '＊',
            'behavioralDisorderSupportCooperation' => '＊',
            'hospitalized' => '＊',
            'longHospitalized' => '＊',
            'coaching' => '＊',
            'vitalFunctionsImprovement1' => '＊',
            'vitalFunctionsImprovement2' => '＊',
            'note' => '備考',
            'userId' => $this->examples->contracts[1]->userId,
            'assigneeId1' => $this->examples->shifts[0]->assignees[0]->staffId,
            'assigneeId2' => $this->examples->shifts[0]->assignees[1]->staffId,
            'assignerId' => $this->examples->shifts[0]->assignerId,
            'task' => Task::assessment()->value(),
            'startMinute' => self::START_MINUTE,
            'endMinute' => self::END_MINUTE,
            'totalDuration' => self::TOTAL_DURATION,
            'dwsHome' => 0,
            'visitingCare' => 0,
            'outingSupport' => 0,
            'physicalCare' => 0,
            'housework' => 0,
            'comprehensive' => 0,
            'commAccompany' => 0,
            'ownExpense' => 0,
            'other' => self::OTHER,
            'resting' => self::RESTING,
        ];
    }
}
