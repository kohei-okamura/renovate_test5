<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Shift;

use Domain\Common\Carbon;
use Domain\Common\Schedule;
use Domain\ServiceCode\ServiceCode;
use PhpOffice\PhpSpreadsheet\Shared\Date;

/**
 * 勤務シフトの配列からドメインモデルを生成するサービス.
 */
final class ShiftUtils
{
    /**
     * 勤務シフトの配列から勤務シフトドメインモデルを生成する.
     *
     * @param array $shift
     * @return \Domain\Shift\Shift
     */
    public static function fromAssoc(array $shift): Shift
    {
        $assignees = [...self::createAssignees($shift)];
        $options = [...self::createOptions($shift)];
        $durations = [...self::createDurations($shift)];
        $date = Carbon::parse(Date::excelToDateTimeObject($shift['date'], 'Asia/Tokyo'));
        $start = $date->addMinutes($shift['startMinute']);
        $end = $shift['startMinute'] <= $shift['endMinute']
            ? $date->addMinutes($shift['endMinute'])
            : $date->addMinutes($shift['endMinute'])->addDay();
        return Shift::create([
            'task' => Task::from($shift['task']),
            'serviceCode' => $shift['serviceCode'] === null
                ? null
                : ServiceCode::fromString($shift['serviceCode']),
            'userId' => $shift['userId'],
            'officeId' => $shift['officeId'],
            'contractId' => $shift['contractId'],
            'assignerId' => $shift['assignerId'],
            'assignees' => $assignees,
            'headcount' => count($assignees),
            'schedule' => Schedule::create([
                'start' => $start,
                'end' => $end,
                'date' => $date,
            ]),
            'durations' => $durations,
            'options' => $options,
            'note' => $shift['note'],
            'isConfirmed' => false,
            'createdAt' => Carbon::now(),
            'updatedAt' => Carbon::now(),
        ]);
    }

    /**
     * 勤務シフトの配列から担当スタッフを生成する.
     *
     * @param array $shift
     * @return \Domain\Shift\Assignee[]
     */
    private static function createAssignees(array $shift): iterable
    {
        yield Assignee::create([
            'sort_order' => 0,
            'staffId' => $shift['assigneeId1'],
            'isUndecided' => false,
            'isTraining' => $shift['isTraining1'] === '＊' ?: false,
        ]);
        yield from $shift['assigneeId2'] === null
            ? []
            : [
                Assignee::create([
                    'sort_order' => 1,
                    'staffId' => $shift['assigneeId2'],
                    'isUndecided' => false,
                    'isTraining' => $shift['isTraining2'] === '＊' ?: false,
                ]),
            ];
    }

    /**
     * 勤務シフトの配列からサービスオプションを生成する.
     *
     * @param array $shift
     * @return ServiceOption[]
     */
    private static function createOptions(array $shift): iterable
    {
        if ($shift['notificationEnabled'] === '＊') {
            yield ServiceOption::notificationEnabled();
        }
        if ($shift['oneOff'] === '＊') {
            yield ServiceOption::oneOff();
        }
        if ($shift['firstTime'] === '＊') {
            yield ServiceOption::firstTime();
        }
        if ($shift['emergency'] === '＊') {
            yield ServiceOption::emergency();
        }
        if ($shift['sucking'] === '＊') {
            yield ServiceOption::sucking();
        }
        if ($shift['welfareSpecialistCooperation'] === '＊') {
            yield ServiceOption::welfareSpecialistCooperation();
        }
        if ($shift['plannedByNovice'] === '＊') {
            yield ServiceOption::plannedByNovice();
        }
        if ($shift['providedByBeginner'] === '＊') {
            yield ServiceOption::providedByBeginner();
        }
        if ($shift['providedByCareWorkerForPwsd'] === '＊') {
            yield ServiceOption::providedByCareWorkerForPwsd();
        }
        if ($shift['over20'] === '＊') {
            yield ServiceOption::over20();
        }
        if ($shift['over50'] === '＊') {
            yield ServiceOption::over50();
        }
        if ($shift['behavioralDisorderSupportCooperation'] === '＊') {
            yield ServiceOption::behavioralDisorderSupportCooperation();
        }
        if ($shift['hospitalized'] === '＊') {
            yield ServiceOption::hospitalized();
        }
        if ($shift['longHospitalized'] === '＊') {
            yield ServiceOption::longHospitalized();
        }
        if ($shift['coaching'] === '＊') {
            yield ServiceOption::coaching();
        }
        if ($shift['vitalFunctionsImprovement1'] === '＊') {
            yield ServiceOption::vitalFunctionsImprovement1();
        }
        if ($shift['vitalFunctionsImprovement2'] === '＊') {
            yield ServiceOption::vitalFunctionsImprovement2();
        }
    }

    /**
     * 勤務シフトの配列から勤務時間を生成する.
     *
     * @param array $shift
     * @return \Domain\Shift\Duration[]
     */
    private static function createDurations(array $shift): iterable
    {
        $task = Task::from($shift['task']);
        $resting = (int)$shift['resting'];
        $totalDuration = (int)$shift['totalDuration'];
        $physicalCare = (int)$shift['physicalCare'];
        $housework = (int)$shift['housework'];
        $visitingCare = (int)$shift['visitingCare'];
        $outingSupport = (int)$shift['outingSupport'];
        $restingDurations = $resting === 0
            ? []
            : [Duration::create(['activity' => Activity::resting(), 'duration' => $resting])];
        if ($task->equals(Task::ltcsPhysicalCareAndHousework())) {
            // 介保：身体・生活の場合は「身体」「生活」「休憩」に入力された値をそのまま用いる.
            yield Duration::create(['activity' => Activity::ltcsPhysicalCare(), 'duration' => $physicalCare]);
            yield Duration::create(['activity' => Activity::ltcsHousework(), 'duration' => $housework]);
            yield from $restingDurations;
        } elseif ($task->equals(Task::dwsVisitingCareForPwsd())) {
            // 重訪の場合は「重訪」「移動加算」「休憩」に入力された値をそのまま用いる.
            yield Duration::create(['activity' => Activity::dwsVisitingCareForPwsd(), 'duration' => $visitingCare]);
            yield from $outingSupport === 0
                ? []
                : [Duration::create(['activity' => Activity::dwsOutingSupportForPwsd(), 'duration' => $outingSupport])];
            yield from $restingDurations;
        } elseif ($resting === 0) {
            // 休憩なしの場合は合計時間をそのまま用いる.
            yield from $task
                ->toActivitiesSeq()
                ->map(fn (Activity $x): Duration => Duration::create(['activity' => $x, 'duration' => $totalDuration]));
        } else {
            // 休憩がある場合は合計時間から休憩時間を引く.
            yield from $task->toActivitiesSeq()->map(fn (Activity $x): Duration => Duration::create([
                'activity' => $x,
                'duration' => $totalDuration - $resting,
            ]));
            yield from $restingDurations;
        }
    }
}
