<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Validations\Rules;

use App\Validations\CustomValidator;
use Domain\Shift\Activity;
use Domain\Shift\Task;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Validations\Rules\HaveIntegrityOfRule} のテスト.
 */
final class HaveIntegrityOfRuleTest extends Test
{
    use ExamplesConsumer;
    use MatchesSnapshots;
    use MockeryMixin;
    use RuleTestSupport;
    use UnitSupport;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachTest(function (self $self): void {
        });
        self::beforeEachSpec(function (self $self): void {
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_validateHaveIntegrityOf(): void
    {
        $this->should(
            'pass when valid task and durations given',
            function (int $task, array $durations): void {
                $this->assertTrue(
                    $this->buildSpecifiedValidator($task, $durations)->passes()
                );
            },
            ['examples' => [
                'dwsPhysicalCare' => [
                    Task::dwsPhysicalCare()->value(),
                    [
                        ['activity' => Activity::dwsPhysicalCare()->value()],
                    ],
                ],
                'dwsHousework' => [
                    Task::dwsHousework()->value(),
                    [
                        ['activity' => Activity::dwsHousework()->value()],
                    ],
                ],
                'dwsAccompanyWithPhysicalCare' => [
                    Task::dwsAccompanyWithPhysicalCare()->value(),
                    [
                        ['activity' => Activity::dwsAccompanyWithPhysicalCare()->value()],
                    ],
                ],
                'dwsAccompany' => [
                    Task::dwsAccompany()->value(),
                    [
                        ['activity' => Activity::dwsAccompany()->value()],
                    ],
                ],
                'dwsVisitingCareForPwsd' => [
                    Task::dwsVisitingCareForPwsd()->value(),
                    [
                        ['activity' => Activity::dwsVisitingCareForPwsd()->value()],
                    ],
                ],
                'ltcsPhysicalCare' => [
                    Task::ltcsPhysicalCare()->value(),
                    [
                        ['activity' => Activity::ltcsPhysicalCare()->value()],
                    ],
                ],
                'ltcsHousework' => [
                    Task::ltcsHousework()->value(),
                    [
                        ['activity' => Activity::ltcsHousework()->value()],
                    ],
                ],
                'ltcsPhysicalCareAndHousework' => [
                    Task::ltcsPhysicalCareAndHousework()->value(),
                    [
                        ['activity' => Activity::ltcsPhysicalCare()->value()],
                        ['activity' => Activity::ltcsHousework()->value()],
                    ],
                ],
                'commAccompanyWithPhysicalCare' => [
                    Task::commAccompanyWithPhysicalCare()->value(),
                    [
                        ['activity' => Activity::commAccompanyWithPhysicalCare()->value()],
                    ],
                ],
                'commAccompany' => [
                    Task::commAccompany()->value(),
                    [
                        ['activity' => Activity::commAccompany()->value()],
                    ],
                ],
                'comprehensive' => [
                    Task::comprehensive()->value(),
                    [
                        ['activity' => Activity::comprehensive()->value()],
                    ],
                ],
                'ownExpense' => [
                    Task::ownExpense()->value(),
                    [
                        ['activity' => Activity::ownExpense()->value()],
                    ],
                ],
                'fieldwork' => [
                    Task::fieldwork()->value(),
                    [
                        ['activity' => Activity::fieldwork()->value()],
                    ],
                ],
                'assessment' => [
                    Task::assessment()->value(),
                    [
                        ['activity' => Activity::assessment()->value()],
                    ],
                ],
                'visit' => [
                    Task::visit()->value(),
                    [
                        ['activity' => Activity::visit()->value()],
                    ],
                ],
                'officeWork' => [
                    Task::officeWork()->value(),
                    [
                        ['activity' => Activity::officeWork()->value()],
                    ],
                ],
                'sales' => [
                    Task::sales()->value(),
                    [
                        ['activity' => Activity::sales()->value()],
                    ],
                ],
                'meeting' => [
                    Task::meeting()->value(),
                    [
                        ['activity' => Activity::meeting()->value()],
                    ],
                ],
                'other' => [
                    Task::other()->value(),
                    [
                        ['activity' => Activity::other()->value()],
                    ],
                ],
                'with resting' => [
                    Task::dwsPhysicalCare()->value(),
                    [
                        ['activity' => Activity::dwsPhysicalCare()->value()],
                        ['activity' => Activity::resting()->value()],
                    ],
                ],
                'dwsVisitingCareForPwsd with outing support' => [
                    Task::dwsVisitingCareForPwsd()->value(),
                    [
                        ['activity' => Activity::dwsVisitingCareForPwsd()->value()],
                        ['activity' => Activity::dwsOutingSupportForPwsd()->value()],
                    ],
                ],
                'invalid activity' => [
                    Task::dwsPhysicalCare()->value(),
                    [
                        ['activity' => -1],
                    ],
                ],
                'invalid task' => [
                    -1,
                    [
                        ['activity' => Activity::dwsPhysicalCare()->value()],
                    ],
                ],
            ],
            ]
        );
        $this->should(
            'fail when invalid task and durations given',
            function (int $task, array $durations): void {
                $this->assertTrue(
                    $this->buildSpecifiedValidator($task, $durations)->fails()
                );
            },
            ['examples' => [
                'task different from activity' => [
                    Task::dwsPhysicalCare()->value(),
                    [
                        ['activity' => Activity::ltcsPhysicalCare()->value()],
                    ],
                ],
                'not dwsVisitingCareForPwsd with outing support' => [
                    Task::dwsPhysicalCare()->value(),
                    [
                        ['activity' => Activity::dwsPhysicalCare()->value()],
                        ['activity' => Activity::dwsOutingSupportForPwsd()->value()],
                    ],
                ],
            ],
            ]
        );
    }

    /**
     * バリデータを生成する.
     *
     * @param int $task
     * @param array $durations
     * @return CustomValidator
     */
    private function buildSpecifiedValidator(int $task, array $durations): CustomValidator
    {
        return $this->buildCustomValidator(
            ['task' => $task, 'durations' => $durations],
            ['durations' => 'have_integrity_of:task'],
        );
    }
}
