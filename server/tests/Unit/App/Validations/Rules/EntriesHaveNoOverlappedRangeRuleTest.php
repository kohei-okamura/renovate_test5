<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Validations\Rules;

use Domain\Project\LtcsProjectServiceCategory;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Validations\Rules\EntriesHaveNoOverlappedRangeRule} のテスト.
 */
final class EntriesHaveNoOverlappedRangeRuleTest extends Test
{
    use ExamplesConsumer;
    use MockeryMixin;
    use RuleTestSupport;
    use UnitSupport;

    /**
     * @test
     * @return void
     */
    public function describe_validateEntriesHaveNoOverlappedRange(): void
    {
        $this->should('pass when plans contain invalid date', function (): void {
            $validator = $this->buildCustomValidator(
                [
                    'entries' => [
                        $this->createEntryParams([
                            'slot' => [
                                'start' => '12:00',
                                'end' => '18:00',
                            ],
                            'plans' => ['error'],
                        ]),
                    ],
                ],
                ['entries.*.plans.*' => 'entries_have_no_overlapped_range:plans'],
            );
            $this->assertTrue($validator->passes());
        });
        $this->should('pass when start is invalid', function (): void {
            $validator = $this->buildCustomValidator(
                [
                    'entries' => [
                        $this->createEntryParams([
                            'slot' => [
                                'start' => 'error',
                                'end' => '18:00',
                            ],
                            'plans' => ['2020-10-10'],
                        ]),
                    ],
                ],
                ['entries.*.plans.*' => 'entries_have_no_overlapped_range:plans'],
            );
            $this->assertTrue($validator->passes());
        });
        $this->should('pass when end is invalid', function (): void {
            $validator = $this->buildCustomValidator(
                [
                    'entries' => [
                        $this->createEntryParams([
                            'slot' => [
                                'start' => '12:00',
                                'end' => 'error',
                            ],
                            'plans' => ['2020-10-10'],
                        ]),
                    ],
                ],
                ['entries.*.plans.*' => 'entries_have_no_overlapped_range:plans'],
            );
            $this->assertTrue($validator->passes());
        });
        $this->should('when slots are overlapped and plans are duplicated', function (): void {
            $validator = $this->buildCustomValidator(
                [
                    'entries' => [
                        $this->createEntryParams([
                            'slot' => [
                                'start' => '12:00',
                                'end' => '18:00',
                            ],
                            'plans' => ['2020-10-10', '2020-10-11'],
                        ]),
                        $this->createEntryParams([
                            'slot' => [
                                'start' => '12:00',
                                'end' => '18:00',
                            ],
                            'plans' => ['2020-10-11', '2020-10-12'],
                        ]),
                    ],
                ],
                ['entries.*.plans.*' => 'entries_have_no_overlapped_range:plans'],
            );
            $this->assertTrue($validator->fails());
        });
        $examples = [
            'when slots are overlapped but it is own expense service' => [
                $this->createEntryParams([
                    'category' => LtcsProjectServiceCategory::ownExpense()->value(),
                    'slot' => [
                        'start' => '08:00',
                        'end' => '10:00',
                    ],
                    'plans' => ['2020-10-10', '2020-10-11'],
                ]),
                $this->createEntryParams([
                    'slot' => [
                        'start' => '08:00',
                        'end' => '10:00',
                    ],
                    'plans' => ['2020-10-10', '2020-10-11'],
                ]),
            ],
            'when no slots are overlapped and plans are duplicated' => [
                $this->createEntryParams([
                    'slot' => [
                        'start' => '08:00',
                        'end' => '10:00',
                    ],
                    'plans' => ['2020-10-10', '2020-10-11'],
                ]),
                $this->createEntryParams([
                    'slot' => [
                        'start' => '10:00',
                        'end' => '12:00',
                    ],
                    'plans' => ['2020-10-10', '2020-10-11'],
                ]),
                $this->createEntryParams([
                    'slot' => [
                        'start' => '12:00',
                        'end' => '18:00',
                    ],
                    'plans' => ['2020-10-10', '2020-10-11'],
                ]),
            ],
            'when no slots are overlapped and no plans are duplicated' => [
                $this->createEntryParams([
                    'slot' => [
                        'start' => '08:00',
                        'end' => '10:00',
                    ],
                    'plans' => ['2020-10-10', '2020-10-11'],
                ]),
                $this->createEntryParams([
                    'slot' => [
                        'start' => '10:00',
                        'end' => '12:00',
                    ],
                    'plans' => ['2020-10-12', '2020-10-13'],
                ]),
                $this->createEntryParams([
                    'slot' => [
                        'start' => '12:00',
                        'end' => '18:00',
                    ],
                    'plans' => ['2020-10-14', '2020-10-15'],
                ]),
            ],
            'when slots are overlapped and no plans are duplicated' => [
                $this->createEntryParams([
                    'slot' => [
                        'start' => '08:00',
                        'end' => '16:00',
                    ],
                    'plans' => ['2020-10-10', '2020-10-11'],
                ]),
                $this->createEntryParams([
                    'slot' => [
                        'start' => '10:00',
                        'end' => '12:00',
                    ],
                    'plans' => ['2020-10-12', '2020-10-13'],
                ]),
                $this->createEntryParams([
                    'slot' => [
                        'start' => '09:00',
                        'end' => '18:00',
                    ],
                    'plans' => ['2020-10-14', '2020-10-15'],
                ]),
            ],
            'when some invalid slots are contained' => [
                $this->createEntryParams([
                    'slot' => [
                        'start' => '08:00',
                        'end' => '10:00',
                    ],
                    'plans' => ['2020-10-10', '2020-10-11'],
                ]),
                $this->createEntryParams([
                    'slot' => [
                        'start' => '10:00',
                        'end' => '12:00',
                    ],
                    'plans' => ['2020-10-10', '2020-10-11'],
                ]),
                $this->createEntryParams([
                    'slot' => [
                        'start' => 'error',
                        'end' => '18:00',
                    ],
                    'plans' => ['2020-10-10', '2020-10-11'],
                ]),
            ],
            'when some invalid plans are contained' => [
                $this->createEntryParams([
                    'slot' => [
                        'start' => '12:00',
                        'end' => '18:00',
                    ],
                    'plans' => ['2020-10-10', '2020-10-11'],
                ]),
                $this->createEntryParams([
                    'slot' => [
                        'start' => '12:00',
                        'end' => '18:00',
                    ],
                    'plans' => ['2020-10-12', '2020-10-13'],
                ]),
                $this->createEntryParams([
                    'slot' => [
                        'start' => '12:00',
                        'end' => '18:00',
                    ],
                    'plans' => ['error', '2020-10-15'],
                ]),
            ],
        ];
        $this->should(
            'pass',
            function (...$entries): void {
                $validator = $this->buildCustomValidator(
                    ['entries' => $entries],
                    ['entries.*.plans.*' => 'entries_have_no_overlapped_range:plans'],
                );
                $this->assertTrue($validator->passes());
            },
            compact('examples'),
        );
    }

    /**
     * 介護保険サービス：予実：サービス情報のパラメーターを生成する.
     *
     * @param array $overwrites
     * @return array
     */
    private function createEntryParams(array $overwrites): array
    {
        return [
            'category' => LtcsProjectServiceCategory::physicalCare()->value(),
            ...$overwrites,
        ];
    }
}
