<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\Billing;

use Domain\Billing\LtcsBillingStatementItem;
use Domain\Billing\LtcsBillingStatementItemSubsidy;
use Domain\ServiceCode\ServiceCode;
use Domain\ServiceCodeDictionary\LtcsServiceCodeCategory;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Test;

/**
 * {@link \Domain\Billing\LtcsBillingStatementItem} のテスト.
 */
final class LtcsBillingStatementItemTest extends Test
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
     * @return \Domain\Billing\LtcsBillingStatementItem
     */
    private function createInstance(array $attrs = []): LtcsBillingStatementItem
    {
        $x = new LtcsBillingStatementItem(
            serviceCode: ServiceCode::fromString('113479'),
            serviceCodeCategory: LtcsServiceCodeCategory::housework(),
            unitScore: 1652,
            count: 18,
            totalScore: 459666,
            subsidies: [
                new LtcsBillingStatementItemSubsidy(
                    count: 14,
                    totalScore: 589929,
                ),
                LtcsBillingStatementItemSubsidy::empty(),
                LtcsBillingStatementItemSubsidy::empty(),
            ],
            note: '270',
        );
        return $x->copy($attrs);
    }
}
