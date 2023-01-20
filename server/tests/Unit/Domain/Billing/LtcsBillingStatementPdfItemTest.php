<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\Billing;

use Domain\Billing\LtcsBillingStatementItem;
use Domain\Billing\LtcsBillingStatementItemSubsidy;
use Domain\Billing\LtcsBillingStatementPdfItem;
use Domain\ServiceCode\ServiceCode;
use Domain\ServiceCodeDictionary\LtcsServiceCodeCategory;
use ScalikePHP\Map;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Test;

/**
 * {@link \Domain\Billing\LtcsBillingStatementPdfItem} のテスト.
 */
final class LtcsBillingStatementPdfItemTest extends Test
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
        $this->should('create LtcsBillingStatementPdfItem', function (): void {
            $actual = new LtcsBillingStatementPdfItem(
                serviceName: '身体介護1',
                serviceCode: '111111',
                unitScore: '250',
                count: '2',
                totalScore: '500',
                subsidyCount: '1',
                subsidyScore: '250',
                note: '摘要',
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
            $actual = LtcsBillingStatementPdfItem::from(
                new LtcsBillingStatementItem(
                    serviceCode: ServiceCode::fromString('111311'),
                    serviceCodeCategory: LtcsServiceCodeCategory::physicalCare(),
                    unitScore: 579,
                    count: 2,
                    totalScore: 1158,
                    subsidies: [
                        new LtcsBillingStatementItemSubsidy(
                            count: 1,
                            totalScore: 579,
                        ),
                        LtcsBillingStatementItemSubsidy::empty(),
                        LtcsBillingStatementItemSubsidy::empty(),
                    ],
                    note: '300',
                ),
                $serviceCodeMap
            );
            $this->assertMatchesModelSnapshot($actual);
        });
    }
}
