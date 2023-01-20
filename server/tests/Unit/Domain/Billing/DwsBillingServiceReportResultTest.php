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
use Domain\Billing\DwsBillingServiceReportPdfResult;
use Domain\Common\Decimal;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * {@link \Domain\Billing\DwsBillingServiceReportPdfResult} のテスト.
 */
final class DwsBillingServiceReportResultTest extends Test
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
        $this->should('create DwsBillingServiceReportPdfResult', function (): void {
            $actual = new DwsBillingServiceReportPdfResult(
                physicalCare: '50',
                physicalCare100: '45',
                physicalCare70: '50',
                physicalCarePwsd: '50',
                accompanyWithPhysicalCare: '60',
                accompanyWithPhysicalCare100: '70',
                accompanyWithPhysicalCare70: '50',
                accompanyWithPhysicalCarePwsd: '90',
                housework: '50',
                housework100: '50',
                housework90: '50',
                houseworkPwsd: '50',
                accompany: '65',
                accompany100: '50',
                accompany90: '75',
                accompanyPwsd: '100',
                accessibleTaxi: '50',
                accessibleTaxi100: '10',
                accessibleTaxi90: '20',
                accessibleTaxiPwsd: '30',
                visitingCareForPwsd: '15',
                outingSupportForPwsd: '50',
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
        $this->should('return DwsBillingServiceReportPdfResult', function (): void {
            $actual = DwsBillingServiceReportPdfResult::from(
                DwsBillingServiceReportAggregate::fromAssoc(
                    [
                        DwsBillingServiceReportAggregateGroup::physicalCare()->value() => [
                            DwsBillingServiceReportAggregateCategory::category100()->value() => Decimal::fromInt(1_5000),
                            DwsBillingServiceReportAggregateCategory::category70()->value() => Decimal::zero(),
                            DwsBillingServiceReportAggregateCategory::categoryPwsd()->value() => Decimal::zero(),
                            DwsBillingServiceReportAggregateCategory::categoryTotal()->value() => Decimal::fromInt(1_5000),
                        ],
                        DwsBillingServiceReportAggregateGroup::accompanyWithPhysicalCare()->value() => [
                            DwsBillingServiceReportAggregateCategory::category100()->value() => Decimal::fromInt(2_5000),
                            DwsBillingServiceReportAggregateCategory::category70()->value() => Decimal::zero(),
                            DwsBillingServiceReportAggregateCategory::categoryPwsd()->value() => Decimal::zero(),
                            DwsBillingServiceReportAggregateCategory::categoryTotal()->value() => Decimal::fromInt(2_5000),
                        ],
                        DwsBillingServiceReportAggregateGroup::housework()->value() => [
                            DwsBillingServiceReportAggregateCategory::category100()->value() => Decimal::fromInt(1_0000),
                            DwsBillingServiceReportAggregateCategory::category90()->value() => Decimal::zero(),
                            DwsBillingServiceReportAggregateCategory::categoryTotal()->value() => Decimal::fromInt(1_0000),
                        ],
                        DwsBillingServiceReportAggregateGroup::accompany()->value() => [
                            DwsBillingServiceReportAggregateCategory::category100()->value() => Decimal::fromInt(3_0000),
                            DwsBillingServiceReportAggregateCategory::category90()->value() => Decimal::zero(),
                            DwsBillingServiceReportAggregateCategory::categoryTotal()->value() => Decimal::fromInt(3_0000),
                        ],
                        DwsBillingServiceReportAggregateGroup::accessibleTaxi()->value() => [
                            DwsBillingServiceReportAggregateCategory::category100()->value() => Decimal::fromInt(3_5000),
                            DwsBillingServiceReportAggregateCategory::category90()->value() => Decimal::zero(),
                            DwsBillingServiceReportAggregateCategory::categoryTotal()->value() => Decimal::fromInt(3_5000),
                        ],
                    ]
                )
            );
            $this->assertMatchesModelSnapshot($actual);
        });
    }
}
