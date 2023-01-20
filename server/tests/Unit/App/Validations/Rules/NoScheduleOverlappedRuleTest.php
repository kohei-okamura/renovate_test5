<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Validations\Rules;

use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Validations\Rules\NoScheduleOverlappedRule} のテスト.
 */
final class NoScheduleOverlappedRuleTest extends Test
{
    use ContextMixin;
    use ExamplesConsumer;
    use RuleTestSupport;
    use UnitSupport;

    /**
     * @test
     * @return void
     */
    public function describe_validateNoScheduleOverlappedRule(): void
    {
        $this->should('pass when start is not date', function (): void {
            $this->assertTrue(
                $this->buildCustomValidator(
                    [
                        'items' => [
                            [
                                'schedule' => [
                                    'start' => 'error',
                                    'end' => '2020-10-10T17:00:00+0900',
                                ],
                                'headcount' => 1,
                            ],
                        ],
                    ],
                    ['items.*.schedule' => 'no_schedule_overlapped:items']
                )
                    ->passes()
            );
        });
        $this->should('pass when end is not date', function (): void {
            $this->assertTrue(
                $this->buildCustomValidator(
                    [
                        'items' => [
                            [
                                'schedule' => [
                                    'start' => '2020-10-10T12:00:00+0900',
                                    'end' => 'error',
                                ],
                                'headcount' => 1,
                            ],
                        ],
                    ],
                    ['items.*.schedule' => 'no_schedule_overlapped:items']
                )
                    ->passes()
            );
        });
        $this->should('pass when headcount is not integer', function (): void {
            $this->assertTrue(
                $this->buildCustomValidator(
                    [
                        'items' => [
                            [
                                'schedule' => [
                                    'start' => '2020-10-10T12:00:00+0900',
                                    'end' => '2020-10-10T17:00:00+0900',
                                ],
                                'headcount' => 'error',
                            ],
                        ],
                    ],
                    ['items.*.schedule' => 'no_schedule_overlapped:items']
                )
                    ->passes()
            );
        });
        $this->should('fail when 部分一致 && 合計人数が3人以上となる予実が既に存在している', function (): void {
            $this->assertTrue(
                $this->buildCustomValidator(
                    [
                        'items' => [
                            [
                                'schedule' => [
                                    'start' => '2020-10-10T12:00:00+0900',
                                    'end' => '2020-10-10T17:00:00+0900',
                                ],
                                'headcount' => 1,
                            ],
                            [
                                'schedule' => [
                                    'start' => '2020-10-10T16:30:00+0900',
                                    'end' => '2020-10-10T20:00:00+0900',
                                ],
                                'headcount' => 2,
                            ],
                        ],
                    ],
                    ['items.*.schedule' => 'no_schedule_overlapped:items']
                )
                    ->fails()
            );
        });
        $this->should('fail when range との重複部分のみの range 同士で重複する', function (): void {
            $this->assertTrue(
                $this->buildCustomValidator(
                    [
                        'items' => [
                            [
                                'schedule' => [
                                    'start' => '2020-10-10T12:00:00+0900',
                                    'end' => '2020-10-10T17:00:00+0900',
                                ],
                                'headcount' => 1,
                            ],
                            [
                                'schedule' => [
                                    'start' => '2020-10-10T16:30:00+0900',
                                    'end' => '2020-10-10T20:00:00+0900',
                                ],
                                'headcount' => 1,
                            ],
                            [
                                'schedule' => [
                                    'start' => '2020-10-10T16:00:00+0900',
                                    'end' => '2020-10-10T17:00:00+0900',
                                ],
                                'headcount' => 1,
                            ],
                        ],
                    ],
                    ['items.*.schedule' => 'no_schedule_overlapped:items']
                )
                    ->fails()
            );
        });
        $this->should('pass when items with overlapped schedules and headcount greater than 2 do not exist', function (): void {
            $this->assertTrue(
                $this->buildCustomValidator(
                    [
                        'items' => [
                            [
                                'schedule' => [
                                    'start' => '2020-10-10T12:00:00+0900',
                                    'end' => '2020-10-10T17:00:00+0900',
                                ],
                                'headcount' => 1,
                            ],
                            [
                                'schedule' => [
                                    'start' => '2020-10-10T16:30:00+0900',
                                    'end' => '2020-10-10T20:00:00+0900',
                                ],
                                'headcount' => 1,
                            ],
                            [
                                'schedule' => [
                                    'start' => '2020-10-10T16:00:00+0900',
                                    'end' => '2020-10-10T16:30:00+0900',
                                ],
                                'headcount' => 1,
                            ],
                        ],
                    ],
                    ['items.*.schedule' => 'no_schedule_overlapped:items']
                )
                    ->passes()
            );
        });
    }
}
