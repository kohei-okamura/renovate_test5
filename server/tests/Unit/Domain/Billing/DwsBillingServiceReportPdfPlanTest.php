<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\Billing;

use Domain\Billing\DwsBillingServiceReportAggregate;
use Domain\Billing\DwsBillingServiceReportAggregateCategory;
use Domain\Billing\DwsBillingServiceReportAggregateGroup;
use Domain\Billing\DwsBillingServiceReportPdfPlan;
use Domain\Common\Decimal;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * {@link \Domain\Billing\DwsBillingServiceReportPdfPlan} のテスト.
 */
final class DwsBillingServiceReportPdfPlanTest extends Test
{
    use CarbonMixin;
    use ExamplesConsumer;
    use MatchesSnapshots;
    use MockeryMixin;
    use UnitSupport;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
    }

    /**
     * @test
     * @return void
     */
    public function describe_construct(): void
    {
        $this->should('create DwsBillingServiceReportPdfPlan', function (): void {
            $actual = new DwsBillingServiceReportPdfPlan(
                physicalCare: '60',
                accompanyWithPhysicalCare: '20',
                housework: '10',
                accompany: '30',
                accessibleTaxi: '50',
                visitingCareForPwsd: '25',
                outingSupportForPwsd: '15',
            );
            $this->assertMatchesModelSnapshot($actual);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_from(): void
    {
        $this->should('return DwsBillingServiceReportPdfPlan', function (): void {
            $actual = DwsBillingServiceReportPdfPlan::from(
                DwsBillingServiceReportAggregate::fromAssoc(
                    [
                        DwsBillingServiceReportAggregateGroup::physicalCare()->value() => [
                            DwsBillingServiceReportAggregateCategory::category100()->value() => Decimal::zero(),
                            DwsBillingServiceReportAggregateCategory::category70()->value() => Decimal::zero(),
                            DwsBillingServiceReportAggregateCategory::categoryPwsd()->value() => Decimal::zero(),
                            DwsBillingServiceReportAggregateCategory::categoryTotal()->value() => Decimal::fromInt(1_0000),
                        ],
                        DwsBillingServiceReportAggregateGroup::accompanyWithPhysicalCare()->value() => [
                            DwsBillingServiceReportAggregateCategory::category100()->value() => Decimal::zero(),
                            DwsBillingServiceReportAggregateCategory::category70()->value() => Decimal::zero(),
                            DwsBillingServiceReportAggregateCategory::categoryPwsd()->value() => Decimal::zero(),
                            DwsBillingServiceReportAggregateCategory::categoryTotal()->value() => Decimal::fromInt(2_0000),
                        ],
                        DwsBillingServiceReportAggregateGroup::housework()->value() => [
                            DwsBillingServiceReportAggregateCategory::category100()->value() => Decimal::zero(),
                            DwsBillingServiceReportAggregateCategory::category90()->value() => Decimal::zero(),
                            DwsBillingServiceReportAggregateCategory::categoryTotal()->value() => Decimal::fromInt(3_0000),
                        ],
                        DwsBillingServiceReportAggregateGroup::accompany()->value() => [
                            DwsBillingServiceReportAggregateCategory::category100()->value() => Decimal::zero(),
                            DwsBillingServiceReportAggregateCategory::category90()->value() => Decimal::zero(),
                            DwsBillingServiceReportAggregateCategory::categoryTotal()->value() => Decimal::fromInt(4_0000),
                        ],
                        DwsBillingServiceReportAggregateGroup::accessibleTaxi()->value() => [
                            DwsBillingServiceReportAggregateCategory::category100()->value() => Decimal::zero(),
                            DwsBillingServiceReportAggregateCategory::category90()->value() => Decimal::zero(),
                            DwsBillingServiceReportAggregateCategory::categoryTotal()->value() => Decimal::fromInt(5_0000),
                        ],
                    ]
                )
            );
            $this->assertMatchesModelSnapshot($actual);
        });
    }
}
