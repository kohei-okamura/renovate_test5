<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Validations\Rules;

use DateTimeInterface;
use Domain\Common\Carbon;
use Domain\Common\Pagination;
use Domain\Common\Schedule;
use Domain\FinderResult;
use Domain\Project\DwsProjectServiceCategory;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\DwsProvisionReportFinderMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Validations\Rules\NoOverlapWithPreviousMonthRule} のテスト.
 */
final class NoOverlapWithPreviousMonthRuleTest extends Test
{
    use CarbonMixin;
    use DwsProvisionReportFinderMixin;
    use ExamplesConsumer;
    use MatchesSnapshots;
    use MockeryMixin;
    use UnitSupport;
    use RuleTestSupport;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->dwsProvisionReportFinder
                ->allows('find')
                ->andReturn(FinderResult::from([$self->examples->dwsProvisionReports[0]], Pagination::create()))
                ->byDefault();
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_validateNoOverlapWithPreviousMonth(): void
    {
        $this->should('pass if plans is not array', function (): void {
            $schedule = $this->createSchedule('2020-04-01');
            $params = [
                'plans' => 'error',
            ];
            $validator = $this->buildCustomValidator(
                $this->buildParams($schedule, $params),
                ['plans' => 'no_overlap_with_previous_month:officeId,userId,providedIn,plans']
            );
            $this->assertTrue($validator->passes());
        });
        $this->should('pass if plans contains own expense service for the first day of a month', function (): void {
            $schedule = $this->createSchedule('2020-04-01');
            $params = [
                'plans' => [
                    [
                        'category' => DwsProjectServiceCategory::ownExpense()->value(),
                        'schedule' => $this->convertScheduleToParams($schedule),
                    ],
                ],
            ];
            $validator = $this->buildCustomValidator(
                $this->buildParams($schedule, $params),
                ['plans' => 'no_overlap_with_previous_month:officeId,userId,providedIn,plans']
            );
            $this->assertTrue($validator->passes());
        });

        $this->should('pass if plans contains other than own expense service but the day is not a first day of a month', function (): void {
            $schedule = $this->createSchedule('2020-04-02');
            $validator = $this->buildCustomValidator(
                $this->buildParams($schedule),
                ['plans' => 'no_overlap_with_previous_month:officeId,userId,providedIn,plans']
            );
            $this->assertTrue($validator->passes());
        });

        $this->should('pass if providedIn is empty', function (): void {
            $schedule = $this->createSchedule('2020-04-01');
            $validator = $this->buildCustomValidator(
                $this->buildParams($schedule, ['providedIn' => '']),
                ['plans' => 'no_overlap_with_previous_month:officeId,userId,providedIn,plans']
            );
            $this->assertTrue($validator->passes());
        });

        $this->should('pass if finder return empty', function (): void {
            $this->dwsProvisionReportFinder
                ->expects('find')
                ->andReturn(FinderResult::from([], Pagination::create()));

            $schedule = $this->createSchedule('2020-04-01');
            $validator = $this->buildCustomValidator(
                $this->buildParams($schedule),
                ['plans' => 'no_overlap_with_previous_month:officeId,userId,providedIn,plans']
            );
            $this->assertTrue($validator->passes());
        });

        $this->should(
            'fail if schedules is overlapping with the last day of a last month',
            function (DwsProjectServiceCategory $category): void {
                $report = $this->examples->dwsProvisionReports[0]->copy([
                    'plans' => [
                        $this->examples->dwsProvisionReports[0]->plans[0]->copy([
                            'category' => $category,
                            'schedule' => $this->createSchedule('2020-03-31', 23, 45, 30),
                        ]),
                    ],
                ]);
                $this->dwsProvisionReportFinder
                    ->expects('find')
                    ->andReturn(FinderResult::from([$report], Pagination::create()));

                $schedule = $this->createSchedule('2020-04-01');
                $validator = $this->buildCustomValidator(
                    $this->buildParams($schedule),
                    ['plans' => 'no_overlap_with_previous_month:officeId,userId,providedIn,plans']
                );
                $this->assertTrue($validator->fails());
            },
            [
                'examples' => [
                    'if category is physicalCare' => [
                        DwsProjectServiceCategory::physicalCare(),
                    ],
                    'if category is housework' => [
                        DwsProjectServiceCategory::housework(),
                    ],
                    'if category is accompanyWithPhysicalCare' => [
                        DwsProjectServiceCategory::accompanyWithPhysicalCare(),
                    ],
                    'if category is accompany' => [
                        DwsProjectServiceCategory::accompany(),
                    ],
                    'if category is visitingCareForPwsd' => [
                        DwsProjectServiceCategory::visitingCareForPwsd(),
                    ],
                ],
            ]
        );

        $this->should('pass if no schedules are overlapping with previous month', function (): void {
            $report = $this->examples->dwsProvisionReports[0]->copy([
                'plans' => [
                    $this->examples->dwsProvisionReports[0]->plans[0]->copy([
                        'category' => DwsProjectServiceCategory::physicalCare(),
                        'schedule' => $this->createSchedule('2020-03-31', 23, 45, 30),
                    ]),
                    $this->examples->dwsProvisionReports[0]->plans[0]->copy([
                        'category' => DwsProjectServiceCategory::physicalCare(),
                        'schedule' => $this->createSchedule('2020-03-31', 1),
                    ]),
                ],
            ]);
            $params = [
                'plans' => [
                    [
                        'category' => DwsProjectServiceCategory::physicalCare()->value(),
                        'schedule' => $this->createScheduleParams('2020-04-01', 0, 15, 30),
                    ],
                    [
                        'category' => DwsProjectServiceCategory::physicalCare()->value(),
                        'schedule' => $this->createScheduleParams('2020-04-01', 16),
                    ],
                ],
            ];
            $this->dwsProvisionReportFinder
                ->expects('find')
                ->andReturn(FinderResult::from([$report], Pagination::create()));

            $schedule = $this->createSchedule('2020-04-01');
            $validator = $this->buildCustomValidator(
                $this->buildParams($schedule, $params),
                ['plans' => 'no_overlap_with_previous_month:officeId,userId,providedIn,plans']
            );
            $this->assertTrue($validator->passes());
        });
    }

    /**
     * スケジュールを作る.
     *
     * @param string $ymd
     * @param int $hour
     * @param int $minute
     * @param int $duration 開始から終了までの分数
     * @return \Domain\Common\Schedule
     */
    private function createSchedule(
        string $ymd,
        int $hour = 0,
        int $minute = 0,
        int $duration = 60,
    ): Schedule {
        $date = Carbon::parse($ymd);
        $start = $date->addHours($hour)->addMinutes($minute);
        return Schedule::create([
            'date' => $date,
            'start' => $start,
            'end' => $start->addMinutes($duration),
        ]);
    }

    /**
     * スケジュールを同じ名前のプロパティを持つ配列に変換する.
     *
     * @param \Domain\Common\Schedule $schedule
     * @return array
     */
    private function convertScheduleToParams(Schedule $schedule): array
    {
        return [
            'date' => $schedule->date->toDateString(),
            'start' => $schedule->start->format(DateTimeInterface::ISO8601),
            'end' => $schedule->end->format(DateTimeInterface::ISO8601),
        ];
    }

    /**
     * スケジュールと同じ名前のプロパティを持つ配列を作る.
     *
     * @param string $ymd
     * @param int $hour
     * @param int $minute
     * @param int $duration 開始から終了までの分数
     * @return array
     */
    private function createScheduleParams(
        string $ymd,
        int $hour = 0,
        int $minute = 0,
        int $duration = 60,
    ): array {
        $schedule = $this->createSchedule($ymd, $hour, $minute, $duration);
        return $this->convertScheduleToParams($schedule);
    }

    /**
     * パラメータを組み立てる.
     *
     * @param \Domain\Common\Schedule $schedule
     * @param array $overwrites
     * @return array
     */
    private function buildParams(Schedule $schedule, array $overwrites = []): array
    {
        return [
            'plans' => [
                [
                    'category' => DwsProjectServiceCategory::physicalCare()->value(),
                    'schedule' => $this->convertScheduleToParams($schedule),
                ],
            ],
            'officeId' => $this->examples->offices[0]->id,
            'userId' => $this->examples->users[0]->id,
            'providedIn' => $schedule->date->firstOfMonth()->format('Y-m'),
            ...$overwrites,
        ];
    }
}
