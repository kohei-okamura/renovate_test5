<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\Billing;

use Domain\Billing\LtcsBillingInvoice;
use Domain\Common\Carbon;
use Domain\Common\DefrayerCategory;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Test;

/**
 * {@link \Domain\Billing\LtcsBillingInvoice} のテスト.
 */
final class LtcsBillingInvoiceTest extends Test
{
    use MatchesSnapshots;
    use UnitSupport;

    /**
     * @test
     * @return void
     */
    public function describe_constructor(): void
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
     * @return \Domain\Billing\LtcsBillingInvoice
     */
    private function createInstance(array $attrs = []): LtcsBillingInvoice
    {
        $x = new LtcsBillingInvoice(
            id: 95169479,
            billingId: 93152532,
            bundleId: 78289017,
            isSubsidy: false,
            defrayerCategory: DefrayerCategory::supportForJapaneseReturneesFromChina(),
            statementCount: 46631,
            totalScore: 10480,
            totalFee: 85420,
            insuranceAmount: 55191,
            subsidyAmount: 33569,
            copayAmount: 80345,
            createdAt: Carbon::create(2013, 1, 21, 11, 16, 21),
            updatedAt: Carbon::create(2017, 2, 4, 16, 9, 56),
        );
        return $x->copy($attrs);
    }
}
