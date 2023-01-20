<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\Office;

use Domain\Billing\DwsServiceDivisionCode;
use Domain\Common\Carbon;
use Domain\Office\DwsSpecifiedTreatmentImprovementAddition;
use Domain\ServiceCodeDictionary\DwsServiceCodeCategory;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * {@link \Domain\Office\DwsSpecifiedTreatmentImprovementAddition} のテスト.
 */
final class DwsSpecifiedTreatmentImprovementAdditionTest extends Test
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
            'return Some of DwsSpecifiedTreatmentImprovementAddition',
            function ($expected, $category): void {
                $actual = DwsSpecifiedTreatmentImprovementAddition::fromDwsServiceCodeCategory($category);
                $this->assertNotEmpty($actual);
                $this->assertSame(
                    $expected,
                    $actual->get()
                );
            },
            [
                'examples' => [
                    'when specifiedTreatmentImprovementAddition1' => [
                        DwsSpecifiedTreatmentImprovementAddition::addition1(),
                        DwsServiceCodeCategory::specifiedTreatmentImprovementAddition1(),
                    ],
                    'when specifiedTreatmentImprovementAddition2' => [
                        DwsSpecifiedTreatmentImprovementAddition::addition2(),
                        DwsServiceCodeCategory::specifiedTreatmentImprovementAddition2(),
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
                DwsSpecifiedTreatmentImprovementAddition $self,
                DwsServiceDivisionCode $serviceDivisionCode,
                int $expect
            ): void {
                $actual = $self->compute(1000, $serviceDivisionCode, Carbon::create(2019, 10));
                $this->assertNotEmpty($actual);
                $this->assertSame($expect, $actual->head());
            },
            ['examples' => [
                'with addition1 and homeHelpService' => [
                    DwsSpecifiedTreatmentImprovementAddition::addition1(), DwsServiceDivisionCode::homeHelpService(), 74,
                ],
                'with addition1 and visitingCareForPwsd' => [
                    DwsSpecifiedTreatmentImprovementAddition::addition1(), DwsServiceDivisionCode::visitingCareForPwsd(), 45,
                ],
                'with addition2 and homeHelpService' => [
                    DwsSpecifiedTreatmentImprovementAddition::addition2(), DwsServiceDivisionCode::homeHelpService(), 58,
                ],
                'with addition2 and visitingCareForPwsd' => [
                    DwsSpecifiedTreatmentImprovementAddition::addition2(), DwsServiceDivisionCode::visitingCareForPwsd(), 36,
                ],
            ]]
        );
        $this->should(
            'compute after 2021-4',
            function (
                DwsSpecifiedTreatmentImprovementAddition $self,
                DwsServiceDivisionCode $serviceDivisionCode,
                int $expect
            ): void {
                $actual = $self->compute(1000, $serviceDivisionCode, Carbon::create(2021, 4));
                $this->assertNotEmpty($actual);
                $this->assertSame($expect, $actual->head());
            },
            ['examples' => [
                'with addition1 and homeHelpService' => [
                    DwsSpecifiedTreatmentImprovementAddition::addition1(), DwsServiceDivisionCode::homeHelpService(), 70,
                ],
                'with addition1 and visitingCareForPwsd' => [
                    DwsSpecifiedTreatmentImprovementAddition::addition1(), DwsServiceDivisionCode::visitingCareForPwsd(), 70,
                ],
                'with addition2 and homeHelpService' => [
                    DwsSpecifiedTreatmentImprovementAddition::addition2(), DwsServiceDivisionCode::homeHelpService(), 55,
                ],
                'with addition2 and visitingCareForPwsd' => [
                    DwsSpecifiedTreatmentImprovementAddition::addition2(), DwsServiceDivisionCode::visitingCareForPwsd(), 55,
                ],
            ]]
        );
        $this->should('not compute before 2019-10', function (): void {
            $self = DwsSpecifiedTreatmentImprovementAddition::addition1();
            $actual = $self->compute(1000, DwsServiceDivisionCode::homeHelpService(), Carbon::create(2019, 9));
            $this->assertEmpty($actual);
        });
    }
}
