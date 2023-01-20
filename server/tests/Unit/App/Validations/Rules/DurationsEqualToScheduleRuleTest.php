<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Validations\Rules;

use App\Validations\CustomValidator;
use Domain\Shift\Activity;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Validations\Rules\DurationsEqualToScheduleRule} のテスト.
 */
final class DurationsEqualToScheduleRuleTest extends Test
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
    public function describe_validateDurationsEqualToSchedule(): void
    {
        $this->should('pass when durations is not array', function (): void {
            $this->assertTrue(
                $this->buildSpecificValidator(
                    null,
                    '00:00',
                    '04:00',
                )->passes()
            );
        });
        $this->should('pass when start is null', function (): void {
            $durations = [
                [
                    'activity' => Activity::dwsVisitingCareForPwsd()->value(),
                    'duration' => 240,
                ],
            ];
            $this->assertTrue(
                $this->buildSpecificValidator(
                    $durations,
                    null,
                    '04:00',
                )->passes()
            );
        });
        $this->should('pass when end is null', function (): void {
            $durations = [
                [
                    'activity' => Activity::dwsVisitingCareForPwsd()->value(),
                    'duration' => 240,
                ],
            ];
            $this->assertTrue(
                $this->buildSpecificValidator(
                    $durations,
                    '00:00',
                    null,
                )->passes()
            );
        });
        $this->should('pass when start is invalid format', function (): void {
            $durations = [
                [
                    'activity' => Activity::dwsVisitingCareForPwsd()->value(),
                    'duration' => 240,
                ],
            ];
            $this->assertTrue(
                $this->buildSpecificValidator(
                    $durations,
                    'error',
                    '04:00',
                )->passes()
            );
        });
        $this->should('pass when end is invalid format', function (): void {
            $durations = [
                [
                    'activity' => Activity::dwsVisitingCareForPwsd()->value(),
                    'duration' => 240,
                ],
            ];
            $this->assertTrue(
                $this->buildSpecificValidator(
                    $durations,
                    '00:00',
                    'error',
                )->passes()
            );
        });
        $this->should('pass when contain any invalid activity', function (): void {
            $durations = [
                [
                    'activity' => 'error',
                    'duration' => 240,
                ],
            ];
            $this->assertTrue(
                $this->buildSpecificValidator(
                    $durations,
                    '00:00',
                    '05:00',
                )->passes()
            );
        });
        $this->should('pass when contain not an integer duration', function (): void {
            $durations = [
                [
                    'activity' => Activity::dwsVisitingCareForPwsd()->value(),
                    'duration' => 240.5,
                ],
            ];
            $this->assertTrue(
                $this->buildSpecificValidator(
                    $durations,
                    '00:00',
                    '05:00',
                )->passes()
            );
        });
        $this->should('pass when durations contain dwsOutingSupportForPwsd', function (): void {
            $durations = [
                [
                    'activity' => Activity::dwsVisitingCareForPwsd()->value(),
                    'duration' => 240,
                ],
                [
                    'activity' => Activity::dwsOutingSupportForPwsd()->value(),
                    'duration' => 30,
                ],
            ];
            $this->assertTrue(
                $this->buildSpecificValidator(
                    $durations,
                    '00:00',
                    '04:00',
                )->passes()
            );
        });
        $this->should('fail when duration is not equal to end diff start', function (): void {
            $durations = [
                [
                    'activity' => Activity::dwsVisitingCareForPwsd()->value(),
                    'duration' => 240,
                ],
            ];
            $this->assertTrue(
                $this->buildSpecificValidator(
                    $durations,
                    '00:00',
                    '05:00',
                )->fails()
            );
        });
    }

    /**
     * バリデータを生成する.
     *
     * @param null|array $durations
     * @param null|string $start
     * @param null|string $end
     * @return \App\Validations\CustomValidator
     */
    private function buildSpecificValidator(?array $durations, ?string $start, ?string $end): CustomValidator
    {
        return $this->buildCustomValidator(
            ['schedule' => ['start' => $start, 'end' => $end], 'durations' => $durations],
            ['durations' => 'durations_equal_to_schedule:schedule.start,schedule.end'],
        );
    }
}
