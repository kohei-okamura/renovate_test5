<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\Billing;

use Domain\Billing\LtcsBillingStatementAggregate;
use Domain\Billing\LtcsBillingStatementAggregateInsurance;
use Domain\Billing\LtcsBillingStatementAggregateSubsidy;
use Domain\Billing\LtcsBillingStatementPdfAggregate;
use Domain\Billing\LtcsServiceDivisionCode;
use Domain\Common\Decimal;
use ScalikePHP\Map;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Test;

/**
 * {@link \Domain\Billing\LtcsBillingStatementPdfAggregate} のテスト.
 */
final class LtcsBillingStatementPdfAggregateTest extends Test
{
    use CarbonMixin;
    use MatchesSnapshots;
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
        $this->should('create LtcsBillingStatementPdfAggregate', function (): void {
            $actual = new LtcsBillingStatementPdfAggregate(
                serviceDivisionCode: '11',
                resolvedServiceDivisionCode: '訪問介護',
                serviceDays: '11',
                plannedScore: '1000',
                managedScore: '2000',
                unmanagedScore: '200',
                totalScore: '2000',
                subsidyTotalScore: '300',
                insuranceUnitCost: '11.4',
                insuranceClaimAmount: '22800',
                insuranceCopayAmount: '2280',
                subsidyClaimAmount: '3420',
                subsidyCopayAmount: '342',
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
        $this->should('return LtcsBillingStatementPdfItem', function (): void {
            $serviceCodeMap = Map::from([
                '111311' => '身体介護3',
            ]);
            $actual = LtcsBillingStatementPdfAggregate::from(
                new LtcsBillingStatementAggregate(
                    serviceDivisionCode: LtcsServiceDivisionCode::homeVisitLongTermCare(),
                    serviceDays: 20,
                    plannedScore: 57878,
                    managedScore: 444238,
                    unmanagedScore: 459666,
                    insurance: new LtcsBillingStatementAggregateInsurance(
                        totalScore: 296956,
                        unitCost: Decimal::fromInt(11_1200),
                        claimAmount: 539297,
                        copayAmount: 194157,
                    ),
                    subsidies: [
                        new LtcsBillingStatementAggregateSubsidy(
                            totalScore: 18087,
                            claimAmount: 229439,
                            copayAmount: 351933,
                        ),
                        LtcsBillingStatementAggregateSubsidy::empty(),
                        LtcsBillingStatementAggregateSubsidy::empty(),
                    ],
                ),
            );
            $this->assertMatchesModelSnapshot($actual);
        });
    }
}
