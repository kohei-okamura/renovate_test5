<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Validations\Rules;

use DateTimeInterface;
use Domain\Common\Carbon;
use Domain\Project\DwsProjectServiceCategory;
use Domain\Shift\ServiceOption;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Validations\Rules\NoOvertimeRule} のテスト.
 */
final class NoOvertimeRuleTest extends Test
{
    use CarbonMixin;
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
            // TODO: 各テストケース（メソッド）の実行前に行う処理（準備）を記述する
        });
        self::beforeEachSpec(function (self $self): void {
            // TODO: 各テストケース（メソッド）の実行前に行う処理（準備）を記述する
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_validateNoOvertime(): void
    {
        $this->should('pass when category is invalid', function (): void {
            $start = Carbon::now()->lastOfMonth();
            $this->assertTrue(
                $this->buildCustomValidator(
                    [
                        'results' => [
                            [
                                'category' => self::INVALID_ENUM_VALUE,
                                'schedule' => [
                                    'start' => $start->format(DateTimeInterface::ISO8601),
                                    'end' => $start->addHour()->format(DateTimeInterface::ISO8601),
                                ],
                                'options' => [],
                            ],
                        ],
                    ],
                    ['results.*.schedule.end' => 'no_overtime:results.*.category,results.*.schedule.start,results.*.options']
                )->passes()
            );
        });
        $this->should('pass when start is invalid', function (): void {
            $start = Carbon::now()->lastOfMonth();
            $this->assertTrue(
                $this->buildCustomValidator(
                    [
                        'results' => [
                            [
                                'category' => DwsProjectServiceCategory::housework()->value(),
                                'schedule' => [
                                    'start' => 'Error',
                                    'end' => $start->addHour()->format(DateTimeInterface::ISO8601),
                                ],
                                'options' => [],
                            ],
                        ],
                    ],
                    ['results.*.schedule.end' => 'no_overtime:results.*.category,results.*.schedule.start,results.*.options']
                )->passes()
            );
        });
        $this->should('pass when end is invalid', function (): void {
            $start = Carbon::now()->lastOfMonth();
            $this->assertTrue(
                $this->buildCustomValidator(
                    [
                        'results' => [
                            [
                                'category' => DwsProjectServiceCategory::housework()->value(),
                                'schedule' => [
                                    'start' => $start->format(DateTimeInterface::ISO8601),
                                    'end' => 'Error',
                                ],
                                'options' => [],
                            ],
                        ],
                    ],
                    ['results.*.schedule.end' => 'no_overtime:results.*.category,results.*.schedule.start,results.*.options']
                )->passes()
            );
        });
        $this->should('pass when category is not home help service', function (): void {
            $start = Carbon::now()->lastOfMonth();
            $this->assertTrue(
                $this->buildCustomValidator(
                    [
                        'results' => [
                            [
                                'category' => DwsProjectServiceCategory::visitingCareForPwsd()->value(),
                                'schedule' => [
                                    'start' => $start->format(DateTimeInterface::ISO8601),
                                    'end' => $start->addHour()->format(DateTimeInterface::ISO8601),
                                ],
                                'options' => [],
                            ],
                        ],
                    ],
                    ['results.*.schedule.end' => 'no_overtime:results.*.category,results.*.schedule.start,results.*.options']
                )->passes()
            );
        });
        $this->should('pass when start is not the last of a month', function (): void {
            $start = Carbon::now()->startOfMonth();
            $this->assertTrue(
                $this->buildCustomValidator(
                    [
                        'results' => [
                            [
                                'category' => DwsProjectServiceCategory::housework()->value(),
                                'schedule' => [
                                    'start' => $start->format(DateTimeInterface::ISO8601),
                                    'end' => $start->addHour()->format(DateTimeInterface::ISO8601),
                                ],
                                'options' => [],
                            ],
                        ],
                    ],
                    ['results.*.schedule.end' => 'no_overtime:results.*.category,results.*.schedule.start,results.*.options']
                )->passes()
            );
        });
        $this->should('pass when end is less than or equal day boundary', function (): void {
            $start = Carbon::parse('2022-03-31 23:45:00');
            $this->assertTrue(
                $this->buildCustomValidator(
                    [
                        'results' => [
                            [
                                'category' => DwsProjectServiceCategory::physicalCare()->value(),
                                'schedule' => [
                                    'start' => $start->format(DateTimeInterface::ISO8601),
                                    'end' => $start->addMinutes(30)->format(DateTimeInterface::ISO8601),
                                ],
                                'options' => [],
                            ],
                        ],
                    ],
                    ['results.*.schedule.end' => 'no_overtime:results.*.category,results.*.schedule.start,results.*.options']
                )->passes()
            );
        });
        $this->should('重研の場合かつ身体介護の場合に1日目23時半、2日目0時半の場合に、バリデーションを通過していること', function (): void {
            $start = Carbon::parse('2022-03-31 23:30:00');
            $this->assertTrue(
                $this->buildCustomValidator(
                    [
                        'results' => [
                            [
                                'category' => DwsProjectServiceCategory::physicalCare()->value(),
                                'schedule' => [
                                    'start' => $start->format(DateTimeInterface::ISO8601),
                                    'end' => $start->addMinutes(60)->format(DateTimeInterface::ISO8601),
                                ],
                                'options' => [ServiceOption::providedByCareWorkerForPwsd()->value()],
                            ],
                        ],
                    ],
                    ['results.*.schedule.end' => 'no_overtime:results.*.category,results.*.schedule.start,results.*.options']
                )->passes()
            );
        });
        $this->should('fail when end is greater than day boundary', function (): void {
            $start = Carbon::parse('2022-03-31 23:45:00');
            $this->assertTrue(
                $this->buildCustomValidator(
                    [
                        'results' => [
                            [
                                'category' => DwsProjectServiceCategory::physicalCare()->value(),
                                'schedule' => [
                                    'start' => $start->format(DateTimeInterface::ISO8601),
                                    'end' => $start->addMinutes(31)->format(DateTimeInterface::ISO8601),
                                ],
                                'options' => [],
                            ],
                        ],
                    ],
                    ['results.*.schedule.end' => 'no_overtime:results.*.category,results.*.schedule.start,results.*.options']
                )->fails()
            );
        });
    }
}
