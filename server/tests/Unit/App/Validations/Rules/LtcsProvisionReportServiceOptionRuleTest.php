<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Validations\Rules;

use Domain\Project\LtcsProjectServiceCategory;
use Domain\Shift\ServiceOption;
use ScalikePHP\Seq;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Validations\Rules\LtcsProvisionReportServiceOptionRule} のテスト.
 */
final class LtcsProvisionReportServiceOptionRuleTest extends Test
{
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
    public function describe_validateLtcsProvisionReportServiceOption(): void
    {
        $this->should('pass when option is invalid', function (): void {
            $this->assertTrue(
                $this->buildCustomValidator(
                    [
                        'entries' => [
                            [
                                'category' => LtcsProjectServiceCategory::physicalCare()->value(),
                                'options' => [self::INVALID_ENUM_VALUE],
                            ],
                        ],
                    ],
                    ['entries.*.options.*' => 'ltcs_provision_report_service_option'],
                )
                    ->passes()
            );
        });
        $this->should('pass when category is invalid', function (): void {
            $this->assertTrue(
                $this->buildCustomValidator(
                    [
                        'entries' => [
                            [
                                'category' => self::INVALID_ENUM_VALUE,
                                'options' => [ServiceOption::sucking()->value()],
                            ],
                        ],
                    ],
                    ['entries.*.options.*' => 'ltcs_provision_report_service_option'],
                )
                    ->passes()
            );
        });
        $this->should(
            'pass',
            function (LtcsProjectServiceCategory $category, array $options): void {
                $this->assertTrue(
                    $this->buildCustomValidator(
                        [
                            'entries' => [
                                [
                                    'category' => $category->value(),
                                    'options' => Seq::fromArray($options)
                                        ->map(fn (ServiceOption $x): int => $x->value())
                                        ->toArray(),
                                ],
                            ],
                        ],
                        ['entries.*.options.*' => 'ltcs_provision_report_service_option'],
                    )
                        ->passes()
                );
            },
            [
                'examples' => [
                    'when category is physicalCare' => [
                        LtcsProjectServiceCategory::physicalCare(),
                        [
                            ServiceOption::firstTime(),
                            ServiceOption::emergency(),
                            ServiceOption::over20(),
                            ServiceOption::over50(),
                            ServiceOption::vitalFunctionsImprovement1(),
                            ServiceOption::vitalFunctionsImprovement2(),
                        ],
                    ],
                    'when category is housework' => [
                        LtcsProjectServiceCategory::housework(),
                        [
                            ServiceOption::firstTime(),
                            ServiceOption::emergency(),
                            ServiceOption::over20(),
                            ServiceOption::over50(),
                            ServiceOption::vitalFunctionsImprovement1(),
                            ServiceOption::vitalFunctionsImprovement2(),
                        ],
                    ],
                    'when category is physicalCareAndHousework' => [
                        LtcsProjectServiceCategory::physicalCareAndHousework(),
                        [
                            ServiceOption::firstTime(),
                            ServiceOption::emergency(),
                            ServiceOption::over20(),
                            ServiceOption::over50(),
                            ServiceOption::vitalFunctionsImprovement1(),
                            ServiceOption::vitalFunctionsImprovement2(),
                        ],
                    ],
                ],
            ]
        );
        $this->should(
            'fail',
            function (LtcsProjectServiceCategory $category, array $options): void {
                $this->assertTrue(
                    $this->buildCustomValidator(
                        [
                            'entries' => [
                                [
                                    'category' => $category->value(),
                                    'options' => Seq::fromArray($options)
                                        ->map(fn (ServiceOption $x): int => $x->value())
                                        ->toArray(),
                                ],
                            ],
                        ],
                        ['entries.*.options.*' => 'ltcs_provision_report_service_option'],
                    )
                        ->fails()
                );
            },
            [
                'examples' => [
                    'when category is physicalCare' => [
                        LtcsProjectServiceCategory::physicalCare(),
                        [
                            ServiceOption::notificationEnabled(),
                        ],
                    ],
                    'when category is housework' => [
                        LtcsProjectServiceCategory::housework(),
                        [
                            ServiceOption::notificationEnabled(),
                        ],
                    ],
                    'when category is physicalCareAndHousework' => [
                        LtcsProjectServiceCategory::physicalCareAndHousework(),
                        [
                            ServiceOption::notificationEnabled(),
                        ],
                    ],
                ],
            ]
        );
        $this->should('return false where category is ownExpose', function () {
            $this->assertTrue(
                $this->buildCustomValidator(
                    [
                        'entries' => [
                            [
                                'category' => LtcsProjectServiceCategory::ownExpense()->value(),
                                'options' => [ServiceOption::coaching()->value()],
                            ],
                        ],
                    ],
                    ['entries.*.options.*' => 'ltcs_provision_report_service_option'],
                )->fails()
            );
        });
    }
}
