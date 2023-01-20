<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\Billing\DwsBilling;

use Domain\Billing\DwsBillingHighCostPayment;
use Illuminate\Support\Arr;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Examples\OrganizationExample;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Test;

/**
 * {@link \Domain\Billing\DwsBillingHighCostPayment} のテスト.
 */
final class DwsBillingHighCostPaymentTest extends Test
{
    use CarbonMixin;
    use ExamplesConsumer;
    use OrganizationExample;
    use UnitSupport;
    use MatchesSnapshots;

    protected DwsBillingHighCostPayment $highCostDwsPayment;

    protected array $values = [];

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (DwsBillingHighCostPaymentTest $self): void {
            $self->values = [
                'subtotalDetailCount' => 10,
                'subtotalFee' => 1000000,
                'subtotalBenefit' => 1000000,
            ];
            $self->highCostDwsPayment = DwsBillingHighCostPayment::create($self->values);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_attrs(): void
    {
        $examples = [
            'subtotalDetailCount' => ['subtotalDetailCount'],
            'subtotalFee' => ['subtotalFee'],
            'subtotalBenefit' => ['subtotalBenefit'],
        ];

        $this->should(
            'have specified attribute',
            function (string $key): void {
                $this->assertSame($this->highCostDwsPayment->get($key), Arr::get($this->values, $key));
            },
            compact('examples')
        );
    }

    /**
     * @test
     * @return void
     */
    public function describe_json(): void
    {
        $this->should('changes to JSON encode results', function (): void {
            $this->assertMatchesJsonSnapshot($this->highCostDwsPayment);
        });
    }
}
