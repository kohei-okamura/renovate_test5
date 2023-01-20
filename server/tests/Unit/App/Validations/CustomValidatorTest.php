<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Validations;

use App\Validations\CustomValidator;
use Domain\Shift\ServiceOption;
use Domain\Shift\Task;
use ScalikePHP\Option;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\DwsBillingStatementFinderMixin;
use Tests\Unit\Mixins\FindContractUseCaseMixin;
use Tests\Unit\Mixins\FindOfficeGroupUseCaseMixin;
use Tests\Unit\Mixins\FindOfficeUseCaseMixin;
use Tests\Unit\Mixins\FindShiftUseCaseMixin;
use Tests\Unit\Mixins\IdentifyContractUseCaseMixin;
use Tests\Unit\Mixins\LookupAttendanceUseCaseMixin;
use Tests\Unit\Mixins\LookupDwsCertificationUseCaseMixin;
use Tests\Unit\Mixins\LookupLtcsInsCardUseCaseMixin;
use Tests\Unit\Mixins\LtcsBillingStatementFinderMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Validations\CustomValidator} のテスト.
 */
class CustomValidatorTest extends Test
{
    use CarbonMixin;
    use ContextMixin;
    use DwsBillingStatementFinderMixin;
    use ExamplesConsumer;
    use FindContractUseCaseMixin;
    use FindOfficeUseCaseMixin;
    use FindOfficeGroupUseCaseMixin;
    use FindShiftUseCaseMixin;
    use IdentifyContractUseCaseMixin;
    use LookupDwsCertificationUseCaseMixin;
    use LookupLtcsInsCardUseCaseMixin;
    use LookupAttendanceUseCaseMixin;
    use LtcsBillingStatementFinderMixin;
    use MockeryMixin;
    use UnitSupport;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (CustomValidatorTest $self) {
            $self->identifyContractUseCase
                ->allows('handle')
                ->andReturn(Option::from($self->examples->shifts[0]))
                ->byDefault();
        });
    }

    /**
     * @test
     * @retrun void
     */
    public function describe_validateShiftAttendanceServiceOption(): void
    {
        $this->should(
            'pass when option is invalid',
            function (): void {
                $this->assertTrue(
                    CustomValidator::make(
                        $this->context,
                        ['options' => [self::INVALID_ENUM_VALUE]],
                        ['options.*' => 'shift_attendance_service_option:task'],
                        [],
                        []
                    )
                        ->passes()
                );
            }
        );
        $this->should(
            'pass when task is invalid',
            function (): void {
                $this->assertTrue(
                    CustomValidator::make(
                        $this->context,
                        ['task' => self::INVALID_ENUM_VALUE, 'options' => [ServiceOption::notificationEnabled()->value()]],
                        ['options.*' => 'shift_attendance_service_option:task'],
                        [],
                        []
                    )
                        ->passes()
                );
            }
        );
        $this->should(
            'pass',
            function (Task $task, array $options): void {
                $this->assertTrue(
                    CustomValidator::make(
                        $this->context,
                        ['task' => $task->value(), 'options' => $options],
                        ['options.*' => 'shift_attendance_service_option:task'],
                        [],
                        []
                    )
                        ->passes()
                );
            },
            [
                'examples' => [
                    'when task is dwsPhysicalCare' => [
                        'task' => Task::dwsPhysicalCare(),
                        'options' => [
                            ServiceOption::notificationEnabled()->value(),
                            ServiceOption::oneOff()->value(),
                            ServiceOption::firstTime()->value(),
                            ServiceOption::emergency()->value(),
                            ServiceOption::sucking()->value(),
                            ServiceOption::welfareSpecialistCooperation()->value(),
                            ServiceOption::plannedByNovice()->value(),
                            ServiceOption::providedByBeginner()->value(),
                            ServiceOption::providedByCareWorkerForPwsd()->value(),
                            ServiceOption::over20()->value(),
                            ServiceOption::over50()->value(),
                        ],
                    ],
                    'when task is dwsHousework' => [
                        'task' => Task::dwsHousework(),
                        'options' => [
                            ServiceOption::notificationEnabled()->value(),
                            ServiceOption::oneOff()->value(),
                            ServiceOption::firstTime()->value(),
                            ServiceOption::emergency()->value(),
                            ServiceOption::sucking()->value(),
                            ServiceOption::welfareSpecialistCooperation()->value(),
                            ServiceOption::plannedByNovice()->value(),
                            ServiceOption::providedByBeginner()->value(),
                            ServiceOption::providedByCareWorkerForPwsd()->value(),
                            ServiceOption::over20()->value(),
                            ServiceOption::over50()->value(),
                        ],
                    ],
                    'when task is dwsAccompanyWithPhysicalCare' => [
                        'task' => Task::dwsAccompanyWithPhysicalCare(),
                        'options' => [
                            ServiceOption::notificationEnabled()->value(),
                            ServiceOption::oneOff()->value(),
                            ServiceOption::firstTime()->value(),
                            ServiceOption::emergency()->value(),
                            ServiceOption::sucking()->value(),
                            ServiceOption::welfareSpecialistCooperation()->value(),
                            ServiceOption::plannedByNovice()->value(),
                            ServiceOption::providedByBeginner()->value(),
                            ServiceOption::providedByCareWorkerForPwsd()->value(),
                            ServiceOption::over20()->value(),
                            ServiceOption::over50()->value(),
                        ],
                    ],
                    'when task is dwsAccompany' => [
                        'task' => Task::dwsAccompany(),
                        'options' => [
                            ServiceOption::notificationEnabled()->value(),
                            ServiceOption::oneOff()->value(),
                            ServiceOption::firstTime()->value(),
                            ServiceOption::emergency()->value(),
                            ServiceOption::sucking()->value(),
                            ServiceOption::welfareSpecialistCooperation()->value(),
                            ServiceOption::plannedByNovice()->value(),
                            ServiceOption::providedByBeginner()->value(),
                            ServiceOption::providedByCareWorkerForPwsd()->value(),
                            ServiceOption::over20()->value(),
                            ServiceOption::over50()->value(),
                        ],
                    ],
                    'when task is dwsVisitingCareForPwsd' => [
                        'task' => Task::dwsVisitingCareForPwsd(),
                        'options' => [
                            ServiceOption::notificationEnabled()->value(),
                            ServiceOption::oneOff()->value(),
                            ServiceOption::firstTime()->value(),
                            ServiceOption::emergency()->value(),
                            ServiceOption::sucking()->value(),
                            ServiceOption::behavioralDisorderSupportCooperation()->value(),
                            ServiceOption::hospitalized()->value(),
                            ServiceOption::longHospitalized()->value(),
                            ServiceOption::coaching()->value(),
                        ],
                    ],
                    'when task is ltcsPhysicalCare' => [
                        'task' => Task::ltcsPhysicalCare(),
                        'options' => [
                            ServiceOption::notificationEnabled()->value(),
                            ServiceOption::oneOff()->value(),
                            ServiceOption::firstTime()->value(),
                            ServiceOption::emergency()->value(),
                            ServiceOption::over20()->value(),
                            ServiceOption::over50()->value(),
                            ServiceOption::vitalFunctionsImprovement1()->value(),
                            ServiceOption::vitalFunctionsImprovement2()->value(),
                        ],
                    ],
                    'when task is ltcsHousework' => [
                        'task' => Task::ltcsHousework(),
                        'options' => [
                            ServiceOption::notificationEnabled()->value(),
                            ServiceOption::oneOff()->value(),
                            ServiceOption::firstTime()->value(),
                            ServiceOption::emergency()->value(),
                            ServiceOption::over20()->value(),
                            ServiceOption::over50()->value(),
                            ServiceOption::vitalFunctionsImprovement1()->value(),
                            ServiceOption::vitalFunctionsImprovement2()->value(),
                        ],
                    ],
                    'when task is ltcsPhysicalCareAndHousework' => [
                        'task' => Task::ltcsPhysicalCareAndHousework(),
                        'options' => [
                            ServiceOption::notificationEnabled()->value(),
                            ServiceOption::oneOff()->value(),
                            ServiceOption::firstTime()->value(),
                            ServiceOption::emergency()->value(),
                            ServiceOption::over20()->value(),
                            ServiceOption::over50()->value(),
                            ServiceOption::vitalFunctionsImprovement1()->value(),
                            ServiceOption::vitalFunctionsImprovement2()->value(),
                        ],
                    ],
                    'when task is commAccompanyWithPhysicalCare' => [
                        'task' => Task::commAccompanyWithPhysicalCare(),
                        'options' => [
                            ServiceOption::notificationEnabled()->value(),
                            ServiceOption::oneOff()->value(),
                        ],
                    ],
                    'when task is commAccompany' => [
                        'task' => Task::commAccompany(),
                        'options' => [
                            ServiceOption::notificationEnabled()->value(),
                            ServiceOption::oneOff()->value(),
                        ],
                    ],
                    'when task is comprehensive' => [
                        'task' => Task::comprehensive(),
                        'options' => [
                            ServiceOption::notificationEnabled()->value(),
                            ServiceOption::oneOff()->value(),
                        ],
                    ],
                    'when task is ownExpense' => [
                        'task' => Task::ownExpense(),
                        'options' => [
                            ServiceOption::notificationEnabled()->value(),
                            ServiceOption::oneOff()->value(),
                        ],
                    ],
                    'when task is fieldwork' => [
                        'task' => Task::fieldwork(),
                        'options' => [
                            ServiceOption::notificationEnabled()->value(),
                            ServiceOption::oneOff()->value(),
                        ],
                    ],
                    'when task is assessment' => [
                        'task' => Task::assessment(),
                        'options' => [
                            ServiceOption::notificationEnabled()->value(),
                            ServiceOption::oneOff()->value(),
                        ],
                    ],
                    'when task is visit' => [
                        'task' => Task::visit(),
                        'options' => [
                            ServiceOption::notificationEnabled()->value(),
                            ServiceOption::oneOff()->value(),
                        ],
                    ],
                    'when task is officeWork' => [
                        'task' => Task::officeWork(),
                        'options' => [
                            ServiceOption::notificationEnabled()->value(),
                            ServiceOption::oneOff()->value(),
                        ],
                    ],
                    'when task is sales' => [
                        'task' => Task::sales(),
                        'options' => [
                            ServiceOption::notificationEnabled()->value(),
                            ServiceOption::oneOff()->value(),
                        ],
                    ],
                    'when task is meeting' => [
                        'task' => Task::meeting(),
                        'options' => [
                            ServiceOption::notificationEnabled()->value(),
                            ServiceOption::oneOff()->value(),
                        ],
                    ],
                    'when task is other' => [
                        'task' => Task::other(),
                        'options' => [
                            ServiceOption::notificationEnabled()->value(),
                            ServiceOption::oneOff()->value(),
                        ],
                    ],
                ],
            ]
        );
        $this->should(
            'fail',
            function (Task $task, array $options): void {
                $this->assertTrue(
                    CustomValidator::make(
                        $this->context,
                        ['task' => $task->value(), 'options' => $options],
                        ['options.*' => 'shift_attendance_service_option:task'],
                        [],
                        []
                    )
                        ->fails()
                );
            },
            [
                'examples' => [
                    'when task is dwsPhysicalCare' => [
                        'task' => Task::dwsPhysicalCare(),
                        'options' => [ServiceOption::behavioralDisorderSupportCooperation()->value()],
                    ],
                    'when task is dwsHousework' => [
                        'task' => Task::dwsHousework(),
                        'options' => [ServiceOption::behavioralDisorderSupportCooperation()->value()],
                    ],
                    'when task is dwsAccompanyWithPhysicalCare' => [
                        'task' => Task::dwsAccompanyWithPhysicalCare(),
                        'options' => [ServiceOption::behavioralDisorderSupportCooperation()->value()],
                    ],
                    'when task is dwsAccompany' => [
                        'task' => Task::dwsAccompany(),
                        'options' => [ServiceOption::behavioralDisorderSupportCooperation()->value()],
                    ],
                    'when task is dwsVisitingCareForPwsd' => [
                        'task' => Task::dwsVisitingCareForPwsd(),
                        'options' => [ServiceOption::welfareSpecialistCooperation()->value()],
                    ],
                    'when task is ltcsPhysicalCare' => [
                        'task' => Task::ltcsPhysicalCare(),
                        'options' => [ServiceOption::welfareSpecialistCooperation()->value()],
                    ],
                    'when task is ltcsHousework' => [
                        'task' => Task::ltcsHousework(),
                        'options' => [ServiceOption::welfareSpecialistCooperation()->value()],
                    ],
                    'when task is ltcsPhysicalCareAndHousework' => [
                        'task' => Task::ltcsPhysicalCareAndHousework(),
                        'options' => [ServiceOption::welfareSpecialistCooperation()->value()],
                    ],
                    'when task is commAccompanyWithPhysicalCare' => [
                        'task' => Task::commAccompanyWithPhysicalCare(),
                        'options' => [ServiceOption::welfareSpecialistCooperation()->value()],
                    ],
                    'when task is commAccompany' => [
                        'task' => Task::commAccompany(),
                        'options' => [ServiceOption::welfareSpecialistCooperation()->value()],
                    ],
                    'when task is comprehensive' => [
                        'task' => Task::comprehensive(),
                        'options' => [ServiceOption::welfareSpecialistCooperation()->value()],
                    ],
                    'when task is ownExpense' => [
                        'task' => Task::ownExpense(),
                        'options' => [ServiceOption::welfareSpecialistCooperation()->value()],
                    ],
                    'when task is fieldwork' => [
                        'task' => Task::fieldwork(),
                        'options' => [ServiceOption::welfareSpecialistCooperation()->value()],
                    ],
                    'when task is assessment' => [
                        'task' => Task::assessment(),
                        'options' => [ServiceOption::welfareSpecialistCooperation()->value()],
                    ],
                    'when task is visit' => [
                        'task' => Task::visit(),
                        'options' => [ServiceOption::welfareSpecialistCooperation()->value()],
                    ],
                    'when task is officeWork' => [
                        'task' => Task::officeWork(),
                        'options' => [ServiceOption::welfareSpecialistCooperation()->value()],
                    ],
                    'when task is sales' => [
                        'task' => Task::sales(),
                        'options' => [ServiceOption::welfareSpecialistCooperation()->value()],
                    ],
                    'when task is meeting' => [
                        'task' => Task::meeting(),
                        'options' => [ServiceOption::welfareSpecialistCooperation()->value()],
                    ],
                    'when task is other' => [
                        'task' => Task::other(),
                        'options' => [ServiceOption::welfareSpecialistCooperation()->value()],
                    ],
                ],
            ]
        );
    }
}
