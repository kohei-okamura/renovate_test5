<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Validations;

use App\Validations\CustomValidator;
use Domain\BankAccount\BankAccountType;
use Domain\Billing\CopayCoordinationResult;
use Domain\Billing\DwsBillingStatus;
use Domain\Billing\LtcsBillingStatus;
use Domain\Billing\LtcsServiceDivisionCode;
use Domain\Common\ContactRelationship;
use Domain\Common\DayOfWeek;
use Domain\Common\DefrayerCategory;
use Domain\Common\Prefecture;
use Domain\Common\Recurrence;
use Domain\Common\ServiceSegment;
use Domain\Common\Sex;
use Domain\Common\TaxCategory;
use Domain\Common\TaxType;
use Domain\Contract\ContractStatus;
use Domain\DwsCertification\CopayCoordinationType;
use Domain\DwsCertification\DwsCertificationAgreementType;
use Domain\DwsCertification\DwsCertificationServiceType;
use Domain\DwsCertification\DwsCertificationStatus;
use Domain\DwsCertification\DwsLevel;
use Domain\DwsCertification\DwsType;
use Domain\LtcsInsCard\LtcsCarePlanAuthorType;
use Domain\LtcsInsCard\LtcsInsCardServiceType;
use Domain\LtcsInsCard\LtcsInsCardStatus;
use Domain\LtcsInsCard\LtcsLevel;
use Domain\Office\Business;
use Domain\Office\DwsSpecifiedTreatmentImprovementAddition;
use Domain\Office\DwsTreatmentImprovementAddition;
use Domain\Office\HomeHelpServiceSpecifiedOfficeAddition;
use Domain\Office\HomeVisitLongTermCareSpecifiedOfficeAddition;
use Domain\Office\LtcsOfficeLocationAddition;
use Domain\Office\LtcsSpecifiedTreatmentImprovementAddition;
use Domain\Office\LtcsTreatmentImprovementAddition;
use Domain\Office\OfficeQualification;
use Domain\Office\OfficeStatus;
use Domain\Office\Purpose;
use Domain\Office\VisitingCareForPwsdSpecifiedOfficeAddition;
use Domain\Permission\Permission;
use Domain\Project\DwsProjectServiceCategory;
use Domain\Project\LtcsProjectAmountCategory;
use Domain\Project\LtcsProjectServiceCategory;
use Domain\ProvisionReport\DwsProvisionReportStatus;
use Domain\ProvisionReport\LtcsProvisionReportStatus;
use Domain\Role\RoleScope;
use Domain\ServiceCodeDictionary\LtcsServiceCodeCategory;
use Domain\ServiceCodeDictionary\Timeframe;
use Domain\Shift\Activity;
use Domain\Shift\ServiceOption;
use Domain\Shift\Task;
use Domain\Staff\Certification;
use Domain\Staff\StaffStatus;
use Domain\User\BillingDestination;
use Domain\User\PaymentMethod;
use Domain\User\UserDwsSubsidyType;
use Lib\Arrays;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\DummyContextMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Validations\EnumValidator} Test.
 */
final class EnumValidatorTest extends Test
{
    use DummyContextMixin;
    use UnitSupport;

    /**
     * @test
     * @return void
     */
    public function describe_validateEnums(): void
    {
        $examples = [
            'Activity' => [
                'activity',
                Activity::all(),
            ],
            'BankAccountType' => [
                'bank_account_type',
                BankAccountType::all(),
            ],
            'BillingDestination' => [
                'billing_destination',
                tap(BillingDestination::all(), function (array &$xs): void {
                    unset($xs[0]);
                }),
            ],
            'Business' => [
                'business',
                Business::all(),
            ],
            'Certification' => [
                'certification',
                Certification::all(),
            ],
            'ContactRelationship' => [
                'contact_relationship',
                ContactRelationship::all(),
            ],
            'ContractStatus' => [
                'contract_status',
                ContractStatus::all(),
            ],
            'CopayCoordinationResult' => [
                'copay_coordination_result',
                CopayCoordinationResult::all(),
            ],
            'CopayCoordinationType' => [
                'copay_coordination_type',
                CopayCoordinationType::all(),
            ],
            'DayOfWeek' => [
                'day_of_week',
                DayOfWeek::all(),
            ],
            'DefrayerCategory' => [
                'defrayer_category',
                DefrayerCategory::all(),
            ],
            'DwsBillingStatus' => [
                'dws_billing_status',
                DwsBillingStatus::all(),
            ],
            'DwsCertificationAgreementType' => [
                'dws_certification_agreement_type',
                DwsCertificationAgreementType::all(),
            ],
            'DwsProjectServiceCategory' => [
                'dws_project_service_category',
                DwsProjectServiceCategory::all(),
            ],
            'DwsCertificationServiceType' => [
                'dws_certification_service_type',
                DwsCertificationServiceType::all(),
            ],
            'DwsCertificationStatus' => [
                'dws_certification_status',
                DwsCertificationStatus::all(),
            ],
            'DwsLevel' => [
                'dws_level',
                DwsLevel::all(),
            ],
            'DwsProvisionReportStatus' => [
                'dws_provision_report_status',
                DwsProvisionReportStatus::all(),
            ],
            'DwsSpecifiedTreatmentImprovementAddition' => [
                'dws_specified_treatment_improvement_addition',
                DwsSpecifiedTreatmentImprovementAddition::all(),
            ],
            'DwsTreatmentImprovementAddition' => [
                'dws_treatment_improvement_addition',
                DwsTreatmentImprovementAddition::all(),
            ],
            'DwsType' => [
                'dws_type',
                DwsType::all(),
            ],
            'HomeHelpServiceSpecifiedOfficeAddition' => [
                'home_help_service_specified_office_addition',
                HomeHelpServiceSpecifiedOfficeAddition::all(),
            ],
            'HomeVisitLongTermCareSpecifiedOfficeAddition' => [
                'home_visit_long_term_care_specified_office_addition',
                HomeVisitLongTermCareSpecifiedOfficeAddition::all(),
            ],
            'LtcsBillingStatus' => [
                'ltcs_billing_status',
                LtcsBillingStatus::all(),
            ],
            'LtcsCarePlanAuthorType' => [
                'ltcs_care_plan_author_type',
                LtcsCarePlanAuthorType::all(),
            ],
            'LtcsInsCardServiceType' => [
                'ltcs_ins_card_service_type',
                LtcsInsCardServiceType::all(),
            ],
            'LtcsInsCardStatus' => [
                'ltcs_ins_card_status',
                LtcsInsCardStatus::all(),
            ],
            'LtcsLevel' => [
                'ltcs_level',
                LtcsLevel::all(),
            ],
            'LtcsServiceCodeCategory' => [
                'ltcs_service_code_category',
                LtcsServiceCodeCategory::all(),
            ],
            'LtcsServiceDivisionCode' => [
                'ltcs_service_division_code',
                LtcsServiceDivisionCode::all(),
            ],
            'LtcsSpecifiedTreatmentImprovementAddition' => [
                'ltcs_specified_treatment_improvement_addition',
                LtcsSpecifiedTreatmentImprovementAddition::all(),
            ],
            'LtcsTreatmentImprovementAddition' => [
                'ltcs_treatment_improvement_addition',
                LtcsTreatmentImprovementAddition::all(),
            ],
            'LtcsOfficeLocationAddition' => [
                'office_location_addition',
                LtcsOfficeLocationAddition::all(),
            ],
            'OfficeStatus' => [
                'office_status',
                OfficeStatus::all(),
            ],
            'PaymentMethod' => [
                'payment_method',
                tap(PaymentMethod::all(), function (array &$xs): void {
                    unset($xs[0]);
                }),
            ],
            'Permission' => [
                'permission',
                Permission::all(),
            ],
            'Prefecture' => [
                'prefecture',
                tap(Prefecture::all(), function (array &$xs): void {
                    unset($xs[0]);
                }),
            ],
            'Purpose' => [
                'purpose',
                Purpose::all(),
            ],
            'OfficeQualification' => [
                'office_qualification',
                OfficeQualification::all(),
            ],
            'Recurrence' => [
                'recurrence',
                Recurrence::all(),
            ],
            'RoleScope' => [
                'role_scope',
                RoleScope::all(),
            ],
            'ServiceOption' => [
                'service_option',
                ServiceOption::all(),
            ],
            'ServiceSegment' => [
                'service_segment',
                ServiceSegment::all(),
            ],
            'Sex' => [
                'sex',
                Sex::all(),
            ],
            'StaffStatus' => [
                'staff_status',
                StaffStatus::all(),
            ],
            'SubsidyType' => [
                'subsidy_type',
                UserDwsSubsidyType::all(),
            ],
            'Task' => [
                'task',
                Task::all(),
            ],
            'TaxCategory' => [
                'tax_category',
                TaxCategory::all(),
            ],
            'TaxType' => [
                'tax_type',
                TaxType::all(),
            ],
            'Timeframe' => [
                'timeframe',
                Timeframe::all(),
            ],
            'LtcsProvisionReportStatus' => [
                'ltcs_provision_report_status',
                LtcsProvisionReportStatus::all(),
            ],
            'LtcsProjectAmountCategory' => [
                'ltcs_project_amount_category',
                LtcsProjectAmountCategory::all(),
            ],
            'LtcsProjectServiceCategory' => [
                'ltcs_project_service_category',
                LtcsProjectServiceCategory::all(),
            ],
            'VisitingCareForPwsdSpecifiedOfficeAddition' => [
                'visiting_care_for_pwsd_specified_office_addition',
                VisitingCareForPwsdSpecifiedOfficeAddition::all(),
            ],
        ];
        $this->specify(
            '正しい値がバリデーションを通過する',
            function (string $ruleName, array $values): void {
                // TODO: `foreach` を使わないようにしたい
                foreach ($values as $value) {
                    $validator = CustomValidator::make(
                        $this->context,
                        ['param' => $value->value()],
                        ['param' => $ruleName],
                        [],
                        []
                    );
                    $actual = $validator->passes();
                    $this->assertTrue($actual);
                }
            },
            compact('examples')
        );
        $this->specify(
            '不正な値（整数）がバリデーションを通過しない',
            function (string $ruleName): void {
                // TODO: INVALID_ENUM_VALUE が不正である保証がない
                $validator = CustomValidator::make(
                    $this->context,
                    ['param' => self::INVALID_ENUM_VALUE],
                    ['param' => $ruleName],
                    [],
                    []
                );
                $actual = $validator->fails();
                $this->assertTrue($actual);
            },
            compact('examples')
        );
        $this->specify(
            '不正な値（文字列）がバリデーションを通過しない',
            function (string $ruleName): void {
                // TODO: 'string' が不正である保証がない
                $validator = CustomValidator::make(
                    $this->context,
                    ['param' => 'string'],
                    ['param' => $ruleName],
                    [],
                    []
                );
                $actual = $validator->fails();
                $this->assertTrue($actual);
            },
            compact('examples')
        );
    }

    /**
     * @test
     * @return void
     */
    public function describe_validatePermissions(): void
    {
        $this->specify('正しいキーのみの配列であればバリデーションを通過する', function (): void {
            $value = Arrays::generate(function (): iterable {
                foreach (Permission::all() as $permission) {
                    yield $permission->value() => true;
                }
            });
            $validator = CustomValidator::make(
                $this->context,
                ['param' => $value],
                ['param' => 'permissions'],
                [],
                []
            );

            $actual = $validator->passes();

            $this->assertTrue($actual);
        });
        $this->specify('キーが数値の場合はバリデーションを通過しない', function (): void {
            // TODO: 「すべての」なのか「いずれか」なのか、どちらにしろテストが雑
            $validator = CustomValidator::make(
                $this->context,
                ['param' => [1 => false]],
                ['param' => 'permissions'],
                [],
                []
            );

            $actual = $validator->fails();

            $this->assertTrue($actual);
        });
        $this->specify('キーが存在しない値の場合はバリデーションを通過しない', function (): void {
            // TODO: 「すべての」なのか「いずれか」なのか、どちらにしろテストが雑
            $validator = CustomValidator::make(
                $this->context,
                ['param' => [self::INVALID_ENUM_VALUE => true]],
                ['param' => 'permissions'],
                [],
                []
            );

            $actual = $validator->fails();

            $this->assertTrue($actual);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_validateBillingDestination(): void
    {
        $this->specify('正しい値であっても列挙子 none の場合はバリデーションを通過しない', function (): void {
            $validator = CustomValidator::make(
                $this->context,
                ['param' => BillingDestination::none()->value()],
                ['param' => 'billing_destination'],
                [],
                []
            );

            $actual = $validator->fails();

            $this->assertTrue($actual);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_validatePaymentMethod(): void
    {
        $this->specify('正しい値であっても列挙子 none の場合はバリデーションを通過しない', function (): void {
            $validator = CustomValidator::make(
                $this->context,
                ['param' => PaymentMethod::none()->value()],
                ['param' => 'payment_method'],
                [],
                []
            );

            $actual = $validator->fails();

            $this->assertTrue($actual);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_validatePrefecture(): void
    {
        $this->specify('正しい値であっても列挙子 none の場合はバリデーションを通過しない', function (): void {
            $validator = CustomValidator::make(
                $this->context,
                ['param' => Prefecture::none()->value()],
                ['param' => 'prefecture'],
                [],
                []
            );

            $actual = $validator->fails();

            $this->assertTrue($actual);
        });
    }
}
