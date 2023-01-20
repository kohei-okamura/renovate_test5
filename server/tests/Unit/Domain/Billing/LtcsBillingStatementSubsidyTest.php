<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\Billing;

use Domain\Billing\LtcsBillingStatementSubsidy;
use Domain\Common\DefrayerCategory;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Test;

/**
 * {@link \Domain\Billing\LtcsBillingStatementSubsidy} のテスト.
 */
final class LtcsBillingStatementSubsidyTest extends Test
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
     * @return \Domain\Billing\LtcsBillingStatementSubsidy
     */
    private function createInstance(array $attrs = []): LtcsBillingStatementSubsidy
    {
        $x = new LtcsBillingStatementSubsidy(
            defrayerCategory: DefrayerCategory::livelihoodProtection(),
            defrayerNumber: '93152532',
            recipientNumber: '78289017',
            benefitRate: 100,
            totalScore: 85167,
            claimAmount: 14923,
            copayAmount: 46631,
        );
        return $x->copy($attrs);
    }
}
