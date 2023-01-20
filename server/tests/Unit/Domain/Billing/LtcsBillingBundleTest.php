<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\Billing;

use Domain\Billing\LtcsBillingBundle;
use Domain\Common\Carbon;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Test;

/**
 * {@link \Domain\Billing\LtcsBillingBundle} のテスト.
 */
final class LtcsBillingBundleTest extends Test
{
    use MatchesSnapshots;
    use UnitSupport;

    /**
     * @test
     * @return void
     */
    public function describe_create(): void
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
            $this->assertMatchesJsonSnapshot($x);
        });
    }

    /**
     * テスト対象のインスタンスを生成する.
     *
     * @param array $attrs
     * @return \Domain\Billing\LtcsBillingBundle
     */
    private function createInstance(array $attrs = []): LtcsBillingBundle
    {
        $values = [
            'id' => 99199065,
            'billingId' => 95169479,
            'providedIn' => Carbon::create(2007, 2, 22),
            'details' => [],
            'createdAt' => Carbon::create(2009, 4, 30, 19, 10, 16),
            'updatedAt' => Carbon::create(2017, 10, 24, 20, 9, 42),
        ];
        return LtcsBillingBundle::create($attrs + $values);
    }
}
