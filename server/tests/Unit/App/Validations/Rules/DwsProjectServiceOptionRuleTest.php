<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Validations\Rules;

use Domain\Project\DwsProjectServiceCategory;
use Domain\Shift\ServiceOption;
use ScalikePHP\Seq;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Validations\Rules\DwsProjectServiceOptionRule} のテスト.
 */
final class DwsProjectServiceOptionRuleTest extends Test
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
    public function describe_validateDwsProjectServiceOption(): void
    {
        $this->should('pass when option is invalid', function (): void {
            $this->assertTrue(
                $this->buildCustomValidator(
                    [
                        'programs' => [
                            [
                                'category' => DwsProjectServiceCategory::physicalCare()->value(),
                                'options' => [self::INVALID_ENUM_VALUE],
                            ],
                        ],
                    ],
                    ['programs.*.options.*' => 'dws_project_service_option'],
                )
                    ->passes()
            );
        });
        $this->should('pass when category is invalid', function (): void {
            $this->assertTrue(
                $this->buildCustomValidator(
                    [
                        'programs' => [
                            [
                                'category' => self::INVALID_ENUM_VALUE,
                                'options' => [ServiceOption::sucking()->value()],
                            ],
                        ],
                    ],
                    ['programs.*.options.*' => 'dws_project_service_option'],
                )
                    ->passes()
            );
        });
        $this->should(
            'pass',
            function (DwsProjectServiceCategory $category, array $options): void {
                $this->assertTrue(
                    $this->buildCustomValidator(
                        [
                            'programs' => [
                                [
                                    'category' => $category->value(),
                                    'options' => Seq::fromArray($options)
                                        ->map(fn (ServiceOption $x): int => $x->value())
                                        ->toArray(),
                                ],
                            ],
                        ],
                        ['programs.*.options.*' => 'dws_project_service_option'],
                    )
                        ->passes()
                );
            },
            [
                'examples' => [
                    'when category is physicalCare' => [
                        DwsProjectServiceCategory::physicalCare(),
                        [
                            ServiceOption::sucking(),
                            ServiceOption::welfareSpecialistCooperation(),
                            ServiceOption::plannedByNovice(),
                            ServiceOption::providedByBeginner(),
                            ServiceOption::providedByCareWorkerForPwsd(),
                            ServiceOption::over20(),
                            ServiceOption::over50(),
                        ],
                    ],
                    'when category is housework' => [
                        DwsProjectServiceCategory::housework(),
                        [
                            ServiceOption::sucking(),
                            ServiceOption::welfareSpecialistCooperation(),
                            ServiceOption::plannedByNovice(),
                            ServiceOption::providedByBeginner(),
                            ServiceOption::providedByCareWorkerForPwsd(),
                            ServiceOption::over20(),
                            ServiceOption::over50(),
                        ],
                    ],
                    'when category is accompanyWithPhysicalCare' => [
                        DwsProjectServiceCategory::accompanyWithPhysicalCare(),
                        [
                            ServiceOption::sucking(),
                            ServiceOption::welfareSpecialistCooperation(),
                            ServiceOption::plannedByNovice(),
                            ServiceOption::providedByBeginner(),
                            ServiceOption::providedByCareWorkerForPwsd(),
                            ServiceOption::over20(),
                            ServiceOption::over50(),
                        ],
                    ],
                    'when category is accompany' => [
                        DwsProjectServiceCategory::accompany(),
                        [
                            ServiceOption::sucking(),
                            ServiceOption::welfareSpecialistCooperation(),
                            ServiceOption::plannedByNovice(),
                            ServiceOption::providedByBeginner(),
                            ServiceOption::providedByCareWorkerForPwsd(),
                            ServiceOption::over20(),
                            ServiceOption::over50(),
                        ],
                    ],
                    'when category is visitingCareForPwsd' => [
                        DwsProjectServiceCategory::visitingCareForPwsd(),
                        [
                            ServiceOption::sucking(),
                            ServiceOption::behavioralDisorderSupportCooperation(),
                            ServiceOption::hospitalized(),
                            ServiceOption::longHospitalized(),
                            ServiceOption::coaching(),
                        ],
                    ],
                ],
            ]
        );
        $this->should(
            'fail',
            function (DwsProjectServiceCategory $category, array $options): void {
                $this->assertTrue(
                    $this->buildCustomValidator(
                        [
                            'programs' => [
                                [
                                    'category' => $category->value(),
                                    'options' => Seq::fromArray($options)
                                        ->map(fn (ServiceOption $x): int => $x->value())
                                        ->toArray(),
                                ],
                            ],
                        ],
                        ['programs.*.options.*' => 'dws_project_service_option'],
                    )
                        ->fails()
                );
            },
            [
                'examples' => [
                    'when category is physicalCare' => [
                        DwsProjectServiceCategory::physicalCare(),
                        [
                            ServiceOption::notificationEnabled(),
                        ],
                    ],
                    'when category is housework' => [
                        DwsProjectServiceCategory::housework(),
                        [
                            ServiceOption::oneOff(),
                        ],
                    ],
                    'when category is accompanyWithPhysicalCare' => [
                        DwsProjectServiceCategory::accompanyWithPhysicalCare(),
                        [
                            ServiceOption::firstTime(),
                        ],
                    ],
                    'when category is accompany' => [
                        DwsProjectServiceCategory::accompany(),
                        [
                            ServiceOption::emergency(),
                        ],
                    ],
                    'when category is visitingCareForPwsd' => [
                        DwsProjectServiceCategory::visitingCareForPwsd(),
                        [
                            ServiceOption::plannedByNovice(),
                        ],
                    ],
                ],
            ]
        );
    }
}
