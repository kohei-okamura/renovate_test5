<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\Billing;

use Domain\Billing\DwsGrantedServiceCode;
use Domain\Billing\DwsServiceDivisionCode;
use Domain\ServiceCodeDictionary\DwsServiceCodeCategory;
use Lib\Exceptions\InvalidArgumentException;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Test;

/**
 * {@link \Domain\Billing\DwsGrantedServiceCode} のテスト.
 */
final class DwsGrantedServiceCodeTest extends Test
{
    use CarbonMixin;
    use ExamplesConsumer;
    use UnitSupport;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (DwsGrantedServiceCodeTest $self): void {
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_getServiceDivisionCode()
    {
        $examples = [
            'when DwsGrantedServiceCode is physicalCare' => [
                DwsGrantedServiceCode::physicalCare(),
                DwsServiceDivisionCode::homeHelpService(),
            ],
            'when DwsGrantedServiceCode is housework' => [
                DwsGrantedServiceCode::housework(),
                DwsServiceDivisionCode::homeHelpService(),
            ],
            'when DwsGrantedServiceCode is accompanyWithPhysicalCare' => [
                DwsGrantedServiceCode::accompanyWithPhysicalCare(),
                DwsServiceDivisionCode::homeHelpService(),
            ],
            'when DwsGrantedServiceCode is accompany' => [
                DwsGrantedServiceCode::accompany(),
                DwsServiceDivisionCode::homeHelpService(),
            ],
            'when DwsGrantedServiceCode is visitingCareForPwsd1' => [
                DwsGrantedServiceCode::visitingCareForPwsd1(),
                DwsServiceDivisionCode::visitingCareForPwsd(),
            ],
            'when DwsGrantedServiceCode is visitingCareForPwsd2' => [
                DwsGrantedServiceCode::visitingCareForPwsd2(),
                DwsServiceDivisionCode::visitingCareForPwsd(),
            ],
            'when DwsGrantedServiceCode is visitingCareForPwsd3' => [
                DwsGrantedServiceCode::visitingCareForPwsd3(),
                DwsServiceDivisionCode::visitingCareForPwsd(),
            ],
            'when DwsGrantedServiceCode is outingSupportForPwsd' => [
                DwsGrantedServiceCode::outingSupportForPwsd(),
                DwsServiceDivisionCode::visitingCareForPwsd(),
            ],
        ];
        $this->should(
            'return proper serviceDivisionCode',
            function (DwsGrantedServiceCode $grantedServiceCode, DwsServiceDivisionCode $serviceDivisionCode): void {
                $this->assertSame(
                    $serviceDivisionCode,
                    $grantedServiceCode->toDwsServiceDivisionCode()
                );
            },
            compact('examples')
        );
        $this->should(
            'throw InvalidArgumentException when DwsGrantedServiceCode is unexpected',
            function (): void {
                $this->assertThrows(InvalidArgumentException::class, function (): void {
                    DwsGrantedServiceCode::none()->toDwsServiceDivisionCode();
                });
            }
        );
    }

    /**
     * @test
     * @return void
     */
    public function describe_fromDwsServiceCodeCategory()
    {
        $examples = [
            'when DwsServiceCodeCategory is physicalCare' => [
                DwsGrantedServiceCode::physicalCare(),
                DwsServiceCodeCategory::physicalCare(),
            ],
            'when DwsServiceCodeCategory is housework' => [
                DwsGrantedServiceCode::housework(),
                DwsServiceCodeCategory::housework(),
            ],
            'when DwsServiceCodeCategory is accompanyWithPhysicalCare' => [
                DwsGrantedServiceCode::accompanyWithPhysicalCare(),
                DwsServiceCodeCategory::accompanyWithPhysicalCare(),
            ],
            'when DwsServiceCodeCategory is accompany' => [
                DwsGrantedServiceCode::accompany(),
                DwsServiceCodeCategory::accompany(),
            ],
            'when DwsServiceCodeCategory is visitingCareForPwsd1' => [
                DwsGrantedServiceCode::visitingCareForPwsd1(),
                DwsServiceCodeCategory::visitingCareForPwsd1(),
            ],
            'when DwsGrantedServiceCode is visitingCareForPwsd2' => [
                DwsGrantedServiceCode::visitingCareForPwsd2(),
                DwsServiceCodeCategory::visitingCareForPwsd2(),
            ],
            'when DwsServiceCodeCategory is visitingCareForPwsd3' => [
                DwsGrantedServiceCode::visitingCareForPwsd3(),
                DwsServiceCodeCategory::visitingCareForPwsd3(),
            ],
            'when DwsServiceCodeCategory is outingSupportForPwsd' => [
                DwsGrantedServiceCode::outingSupportForPwsd(),
                DwsServiceCodeCategory::outingSupportForPwsd(),
            ],
        ];
        $exampleExceptions = [
            'when DwsServiceCodeCategory is specifiedOfficeAddition1' => [
                DwsServiceCodeCategory::specifiedOfficeAddition1(),
            ],
            'when DwsServiceCodeCategory is specifiedOfficeAddition2' => [
                DwsServiceCodeCategory::specifiedOfficeAddition2(),
            ],
            'when DwsServiceCodeCategory is specifiedOfficeAddition3' => [
                DwsServiceCodeCategory::specifiedOfficeAddition3(),
            ],
            'when DwsServiceCodeCategory is specifiedOfficeAddition4' => [
                DwsServiceCodeCategory::specifiedOfficeAddition4(),
            ],
            'when DwsServiceCodeCategory is specifiedAreaAddition' => [
                DwsServiceCodeCategory::specifiedAreaAddition(),
            ],
            'when DwsServiceCodeCategory is emergencyAddition' => [
                DwsServiceCodeCategory::emergencyAddition1(),
            ],
            'when DwsServiceCodeCategory is suckingSupportSystemAddition' => [
                DwsServiceCodeCategory::suckingSupportSystemAddition(),
            ],
            'when DwsServiceCodeCategory is firstTimeAddition' => [
                DwsServiceCodeCategory::firstTimeAddition(),
            ],
            'when DwsServiceCodeCategory is copayCoordinationAddition' => [
                DwsServiceCodeCategory::copayCoordinationAddition(),
            ],
            'when DwsServiceCodeCategory is welfareSpecialistCooperationAddition' => [
                DwsServiceCodeCategory::welfareSpecialistCooperationAddition(),
            ],
            'when DwsServiceCodeCategory is behavioralDisorderSupportCooperationAddition' => [
                DwsServiceCodeCategory::behavioralDisorderSupportCooperationAddition(),
            ],
            'when DwsServiceCodeCategory is treatmentImprovementAddition1' => [
                DwsServiceCodeCategory::treatmentImprovementAddition1(),
            ],
            'when DwsServiceCodeCategory is treatmentImprovementAddition2' => [
                DwsServiceCodeCategory::treatmentImprovementAddition2(),
            ],
            'when DwsServiceCodeCategory is treatmentImprovementAddition3' => [
                DwsServiceCodeCategory::treatmentImprovementAddition3(),
            ],
            'when DwsServiceCodeCategory is treatmentImprovementAddition4' => [
                DwsServiceCodeCategory::treatmentImprovementAddition4(),
            ],
            'when DwsServiceCodeCategory is treatmentImprovementAddition5' => [
                DwsServiceCodeCategory::treatmentImprovementAddition5(),
            ],
            'when DwsServiceCodeCategory is treatmentImprovementSpecialAddition' => [
                DwsServiceCodeCategory::treatmentImprovementSpecialAddition(),
            ],
            'when DwsServiceCodeCategory is specifiedTreatmentImprovementAddition1' => [
                DwsServiceCodeCategory::specifiedTreatmentImprovementAddition1(),
            ],
            'when DwsServiceCodeCategory is specifiedTreatmentImprovementAddition2' => [
                DwsServiceCodeCategory::specifiedTreatmentImprovementAddition2(),
            ],
        ];

        $this->should(
            'return DwsGrantedServiceCode',
            function (DwsGrantedServiceCode $grantedServiceCode, DwsServiceCodeCategory $serviceCodeCategory): void {
                $this->assertSame(
                    $grantedServiceCode,
                    DwsGrantedServiceCode::fromDwsServiceCodeCategory($serviceCodeCategory)
                );
            },
            ['examples' => $examples]
        );
        $this->should(
            'throw InvalidArgumentException when DwsServiceCodeCategory is unexpected',
            function (DwsServiceCodeCategory $serviceCodeCategory): void {
                $this->assertThrows(InvalidArgumentException::class, function () use ($serviceCodeCategory): void {
                    DwsGrantedServiceCode::fromDwsServiceCodeCategory($serviceCodeCategory);
                });
            },
            ['examples' => $exampleExceptions]
        );
    }
}
