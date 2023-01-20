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
 * {@link \App\Validations\Rules\NoScheduleDuplicatedRule} のテスト.
 */
final class NoScheduleDuplicatedRuleTest extends Test
{
    use ContextMixin;
    use ExamplesConsumer;
    use RuleTestSupport;
    use UnitSupport;

    /**
     * @test
     * @return void
     */
    public function describe_validateNoScheduleDuplicated(): void
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
                            ],
                        ],
                    ],
                    ['items.*.schedule' => 'no_schedule_duplicated:items']
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
                            ],
                        ],
                    ],
                    ['items.*.schedule' => 'no_schedule_duplicated:items']
                )
                    ->passes()
            );
        });
        $this->should('pass when overlap does not exist', function (): void {
            $this->assertTrue(
                $this->buildCustomValidator(
                    [
                        'items' => [
                            [
                                'schedule' => [
                                    'start' => '2020-10-10T12:00:00+0900',
                                    'end' => '2020-10-10T17:00:00+0900',
                                ],
                            ],
                            [
                                'schedule' => [
                                    'start' => '2020-10-10T13:00:00+0900',
                                    'end' => '2020-10-10T17:00:00+0900',
                                ],
                            ],
                            [
                                'schedule' => [
                                    'start' => '2020-10-10T12:00:00+0900',
                                    'end' => '2020-10-10T18:00:00+0900',
                                ],
                            ],
                        ],
                    ],
                    ['items.*.schedule' => 'no_schedule_duplicated:items']
                )
                    ->passes()
            );
        });
        $this->should('fail when overlap exists', function (): void {
            $this->assertTrue(
                $this->buildCustomValidator(
                    [
                        'items' => [
                            [
                                'schedule' => [
                                    'start' => '2020-10-10T12:00:00+0900',
                                    'end' => '2020-10-10T17:00:00+0900',
                                ],
                            ],
                            [
                                'schedule' => [
                                    'start' => '2020-10-10T12:00:00+0900',
                                    'end' => '2020-10-10T17:00:00+0900',
                                ],
                            ],
                            [
                                'schedule' => [
                                    'start' => '2020-10-10T13:00:00+0900',
                                    'end' => '2020-10-10T17:00:00+0900',
                                ],
                            ],
                        ],
                    ],
                    ['items.*.schedule' => 'no_schedule_duplicated:items']
                )
                    ->fails()
            );
        });
    }
}
