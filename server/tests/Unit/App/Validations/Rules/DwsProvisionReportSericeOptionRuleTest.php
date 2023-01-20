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
 * {@link \App\Validations\Rules\DwsProvisionReportSericeOptionRule} のテスト.
 */
final class DwsProvisionReportSericeOptionRuleTest extends Test
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
    public function describe_validateDwsProvisionReportServiceOption(): void
    {
        $this->should('pass when option is invalid', function (): void {
            $this->assertTrue(
                $this->buildCustomValidator(
                    [
                        'plans' => [
                            [
                                'category' => DwsProjectServiceCategory::physicalCare()->value(),
                                'options' => [self::INVALID_ENUM_VALUE],
                            ],
                        ],
                    ],
                    ['plans.*.options.*' => 'dws_provision_report_service_option:plans'],
                )
                    ->passes()
            );
        });
        $this->should('pass when option is invalid', function (): void {
            $this->assertTrue(
                $this->buildCustomValidator(
                    [
                        'plans' => [
                            [
                                'category' => self::INVALID_ENUM_VALUE,
                                'options' => [ServiceOption::sucking()->value()],
                            ],
                        ],
                    ],
                    ['plans.*.options.*' => 'dws_provision_report_service_option:plans'],
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
                            'plans' => [
                                [
                                    'category' => $category->value(),
                                    'options' => Seq::fromArray($options)
                                        ->map(fn (ServiceOption $x): int => $x->value())
                                        ->toArray(),
                                ],
                            ],
                        ],
                        ['plans.*.options.*' => 'dws_provision_report_service_option:plans'],
                    )
                        ->passes()
                );
            },
            [
                'examples' => [
                    'when category is physicalCare' => [
                        DwsProjectServiceCategory::physicalCare(),
                        [
                            ServiceOption::firstTime(),
                            ServiceOption::emergency(),
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
                            ServiceOption::firstTime(),
                            ServiceOption::emergency(),
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
                            ServiceOption::firstTime(),
                            ServiceOption::emergency(),
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
                            ServiceOption::firstTime(),
                            ServiceOption::emergency(),
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
                            ServiceOption::firstTime(),
                            ServiceOption::emergency(),
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
                            'plans' => [
                                [
                                    'category' => $category->value(),
                                    'options' => Seq::fromArray($options)
                                        ->map(fn (ServiceOption $x): int => $x->value())
                                        ->toArray(),
                                ],
                            ],
                        ],
                        ['plans.*.options.*' => 'dws_provision_report_service_option:plans'],
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
                            ServiceOption::behavioralDisorderSupportCooperation(),
                        ],
                    ],
                    'when category is accompany' => [
                        DwsProjectServiceCategory::accompany(),
                        [
                            ServiceOption::longHospitalized(),
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
