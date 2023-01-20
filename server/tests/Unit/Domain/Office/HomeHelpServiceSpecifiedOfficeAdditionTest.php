<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\Office;

use Domain\Common\Carbon;
use Domain\Office\HomeHelpServiceSpecifiedOfficeAddition;
use Domain\ServiceCodeDictionary\DwsServiceCodeCategory;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * {@link \Domain\Office\HomeHelpServiceSpecifiedOfficeAddition} のテスト.
 */
final class HomeHelpServiceSpecifiedOfficeAdditionTest extends Test
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
            'return Some of HomeHelpServiceSpecifiedOfficeAddition',
            function ($expected, $category): void {
                $actual = HomeHelpServiceSpecifiedOfficeAddition::fromDwsServiceCodeCategory($category);
                $this->assertNotEmpty($actual);
                $this->assertSame(
                    $expected,
                    $actual->get()
                );
            },
            [
                'examples' => [
                    'when specifiedTreatmentImprovementAddition1' => [
                        HomeHelpServiceSpecifiedOfficeAddition::addition1(),
                        DwsServiceCodeCategory::specifiedOfficeAddition1(),
                    ],
                    'when specifiedTreatmentImprovementAddition2' => [
                        HomeHelpServiceSpecifiedOfficeAddition::addition2(),
                        DwsServiceCodeCategory::specifiedOfficeAddition2(),
                    ],
                    'when specifiedTreatmentImprovementAddition3' => [
                        HomeHelpServiceSpecifiedOfficeAddition::addition3(),
                        DwsServiceCodeCategory::specifiedOfficeAddition3(),
                    ],
                    'when specifiedTreatmentImprovementAddition4' => [
                        HomeHelpServiceSpecifiedOfficeAddition::addition4(),
                        DwsServiceCodeCategory::specifiedOfficeAddition4(),
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
                HomeHelpServiceSpecifiedOfficeAddition $self,
                int $expect
            ): void {
                $actual = $self->compute(1000, Carbon::create(2019, 10));
                $this->assertNotEmpty($actual);
                $this->assertSame($expect, $actual->head());
            },
            ['examples' => [
                'with addition1' => [
                    HomeHelpServiceSpecifiedOfficeAddition::addition1(), 200,
                ],
                'with addition2' => [
                    HomeHelpServiceSpecifiedOfficeAddition::addition2(), 100,
                ],
                'with addition3' => [
                    HomeHelpServiceSpecifiedOfficeAddition::addition3(), 100,
                ],
                'with addition4' => [
                    HomeHelpServiceSpecifiedOfficeAddition::addition4(), 50,
                ],
            ]]
        );
        $this->should('not compute before 2019-10', function (): void {
            $self = HomeHelpServiceSpecifiedOfficeAddition::addition1();
            $actual = $self->compute(
                1000,
                Carbon::create(2019, 9)
            );
            $this->assertEmpty($actual);
        });
    }
}
