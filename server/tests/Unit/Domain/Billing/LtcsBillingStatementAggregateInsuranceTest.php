<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\Billing;

use Domain\Billing\LtcsBillingStatementAggregateInsurance;
use Domain\Common\Decimal;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Test;

/**
 * {@link \Domain\Billing\LtcsBillingStatementAggregateInsurance} のテスト.
 */
final class LtcsBillingStatementAggregateInsuranceTest extends Test
{
    use MatchesSnapshots;
    use UnitSupport;

    /**
     * @test
     * @return void
     */
    public function describe_instance(): void
    {
        $this->should('return an instance', function (): void {
            $x = $this->createInstance();
            $this->assertMatchesModelSnapshot($x);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_json(): void
    {
        $this->should('be able to encode to json', function (): void {
            $x = $this->createInstance();
            $this->assertMatchesJsonSnapshot($x->toJson());
        });
    }

    /**
     * テスト対象のインスタンスを生成する.
     *
     * @param array $attrs
     * @return \Domain\Billing\LtcsBillingStatementAggregateInsurance
     */
    private function createInstance(array $attrs = []): LtcsBillingStatementAggregateInsurance
    {
        $x = new LtcsBillingStatementAggregateInsurance(
            totalScore: 565381,
            unitCost: Decimal::fromInt(578_7800),
            claimAmount: 444238,
            copayAmount: 459666,
        );
        return $x->copy($attrs);
    }
}
