<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\Office;

use Domain\Billing\DwsServiceDivisionCode;
use Domain\Common\Carbon;
use Domain\Office\DwsTreatmentImprovementAddition;
use Domain\ServiceCodeDictionary\DwsServiceCodeCategory;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * {@link \Domain\Office\DwsTreatmentImprovementAddition} のテスト.
 */
final class DwsTreatmentImprovementAdditionTest extends Test
{
    use MatchesSnapshots;
    use MockeryMixin;
    use UnitSupport;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_fromDwsServiceCodeCategory(): void
    {
        $this->should(
            'return Some of DwsTreatmentImprovementAddition',
            function ($expected, $category): void {
                $actual = DwsTreatmentImprovementAddition::fromDwsServiceCodeCategory($category);
                $this->assertNotEmpty($actual);
                $this->assertSame(
                    $expected,
                    $actual->get()
                );
            },
            [
                'examples' => [
                    'when specifiedTreatmentImprovementAddition1' => [
                        DwsTreatmentImprovementAddition::addition1(),
                        DwsServiceCodeCategory::treatmentImprovementAddition1(),
                    ],
                    'when specifiedTreatmentImprovementAddition2' => [
                        DwsTreatmentImprovementAddition::addition2(),
                        DwsServiceCodeCategory::treatmentImprovementAddition2(),
                    ],
                    'when specifiedTreatmentImprovementAddition3' => [
                        DwsTreatmentImprovementAddition::addition3(),
                        DwsServiceCodeCategory::treatmentImprovementAddition3(),
                    ],
                    'when specifiedTreatmentImprovementAddition4' => [
                        DwsTreatmentImprovementAddition::addition4(),
                        DwsServiceCodeCategory::treatmentImprovementAddition4(),
                    ],
                    'when specifiedTreatmentImprovementAddition5' => [
                        DwsTreatmentImprovementAddition::addition5(),
                        DwsServiceCodeCategory::treatmentImprovementAddition5(),
                    ],
                    'when treatmentImprovementSpecialAddition' => [
                        DwsTreatmentImprovementAddition::specialAddition(),
                        DwsServiceCodeCategory::treatmentImprovementSpecialAddition(),
                    ],
                ],
            ]
        );
    }

    /**
     * @test
     * @return void
     */
    public function describe_compute(): void
    {
        $this->should(
            'compute after 2019-10',
            function (
                DwsTreatmentImprovementAddition $self,
                DwsServiceDivisionCode $serviceDivisionCode,
                int $expect
            ): void {
                $actual = $self->compute(10000, $serviceDivisionCode, Carbon::create(2019, 10));
                $this->assertNotEmpty($actual);
                $this->assertSame($expect, $actual->head());
            },
            ['examples' => [
                'with addition1 and homeHelpService' => [
                    DwsTreatmentImprovementAddition::addition1(), DwsServiceDivisionCode::homeHelpService(), 3020,
                ],
                'with addition1 and visitingCareForPwsd' => [
                    DwsTreatmentImprovementAddition::addition1(), DwsServiceDivisionCode::visitingCareForPwsd(), 1910,
                ],
                'with addition2 and homeHelpService' => [
                    DwsTreatmentImprovementAddition::addition2(), DwsServiceDivisionCode::homeHelpService(), 2200,
                ],
                'with addition2 and visitingCareForPwsd' => [
                    DwsTreatmentImprovementAddition::addition2(), DwsServiceDivisionCode::visitingCareForPwsd(), 1390,
                ],
                'with addition3 and homeHelpService' => [
                    DwsTreatmentImprovementAddition::addition3(), DwsServiceDivisionCode::homeHelpService(), 1220,
                ],
                'with addition3 and visitingCareForPwsd' => [
                    DwsTreatmentImprovementAddition::addition3(), DwsServiceDivisionCode::visitingCareForPwsd(), 770,
                ],
                'with addition4 and homeHelpService' => [
                    DwsTreatmentImprovementAddition::addition4(), DwsServiceDivisionCode::homeHelpService(), 1098,
                ],
                'with addition4 and visitingCareForPwsd' => [
                    DwsTreatmentImprovementAddition::addition4(), DwsServiceDivisionCode::visitingCareForPwsd(), 693,
                ],
                'with addition5 and homeHelpService' => [
                    DwsTreatmentImprovementAddition::addition5(), DwsServiceDivisionCode::homeHelpService(), 976,
                ],
                'with addition6 and visitingCareForPwsd' => [
                    DwsTreatmentImprovementAddition::addition5(), DwsServiceDivisionCode::visitingCareForPwsd(), 616,
                ],
            ]]
        );
        $this->should(
            'compute after 2021-4',
            function (
                DwsTreatmentImprovementAddition $self,
                DwsServiceDivisionCode $serviceDivisionCode,
                int $expect
            ): void {
                $actual = $self->compute(10000, $serviceDivisionCode, Carbon::create(2021, 4));
                $this->assertNotEmpty($actual);
                $this->assertSame($expect, $actual->head());
            },
            ['examples' => [
                'with addition1 and homeHelpService' => [
                    DwsTreatmentImprovementAddition::addition1(), DwsServiceDivisionCode::homeHelpService(), 2740,
                ],
                'with addition1 and visitingCareForPwsd' => [
                    DwsTreatmentImprovementAddition::addition1(), DwsServiceDivisionCode::visitingCareForPwsd(), 2000,
                ],
                'with addition2 and homeHelpService' => [
                    DwsTreatmentImprovementAddition::addition2(), DwsServiceDivisionCode::homeHelpService(), 2000,
                ],
                'with addition2 and visitingCareForPwsd' => [
                    DwsTreatmentImprovementAddition::addition2(), DwsServiceDivisionCode::visitingCareForPwsd(), 1460,
                ],
                'with addition3 and homeHelpService' => [
                    DwsTreatmentImprovementAddition::addition3(), DwsServiceDivisionCode::homeHelpService(), 1110,
                ],
                'with addition3 and visitingCareForPwsd' => [
                    DwsTreatmentImprovementAddition::addition3(), DwsServiceDivisionCode::visitingCareForPwsd(), 810,
                ],
                'with addition4 and homeHelpService' => [
                    DwsTreatmentImprovementAddition::addition4(), DwsServiceDivisionCode::homeHelpService(), 999,
                ],
                'with addition4 and visitingCareForPwsd' => [
                    DwsTreatmentImprovementAddition::addition4(), DwsServiceDivisionCode::visitingCareForPwsd(), 729,
                ],
                'with addition5 and homeHelpService' => [
                    DwsTreatmentImprovementAddition::addition5(), DwsServiceDivisionCode::homeHelpService(), 888,
                ],
                'with addition6 and visitingCareForPwsd' => [
                    DwsTreatmentImprovementAddition::addition5(), DwsServiceDivisionCode::visitingCareForPwsd(), 648,
                ],
            ]]
        );
        $this->should('not compute before 2019-10', function (): void {
            $self = DwsTreatmentImprovementAddition::addition1();
            $actual = $self->compute(
                1000,
                DwsServiceDivisionCode::homeHelpService(),
                Carbon::create(2019, 9)
            );
            $this->assertEmpty($actual);
        });
    }
}
