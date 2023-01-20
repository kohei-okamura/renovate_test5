<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\UserBilling;

use Domain\Common\ConsumptionTaxRate;
use Domain\UserBilling\UserBillingOtherItem;
use Illuminate\Support\Arr;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Test;

/**
 * {@link \Domain\UserBilling\UserBillingOtherItem} のテスト
 */
class UserBillingOtherItemTest extends Test
{
    use UnitSupport;
    use MatchesSnapshots;

    protected UserBillingOtherItem $userBillingOtherItem;

    protected array $values = [];

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (UserBillingOtherItemTest $self): void {
            $self->values = [
                'score' => 100,
                'unitCost' => 10,
                'subtotalCost' => 1000,
                'tax' => ConsumptionTaxRate::ten(),
                'medicalDeductionAmount' => 5000,
                'totalAmount' => 1000,
                'copayWithoutTax' => 2000,
                'copayWithTax' => 2200,
            ];
            $self->userBillingOtherItem = UserBillingOtherItem::create($self->values);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_attrs(): void
    {
        $examples = [
            'score' => ['score'],
            'unitCost' => ['unitCost'],
            'subtotalCost' => ['subtotalCost'],
            'tax' => ['tax'],
            'medicalDeductionAmount' => ['medicalDeductionAmount'],
            'totalAmount' => ['totalAmount'],
            'copayWithoutTax' => ['copayWithoutTax'],
            'copayWithTax' => ['copayWithTax'],
        ];
        $this->should(
            'have specified attribute',
            function (string $key): void {
                $this->assertSame($this->userBillingOtherItem->get($key), Arr::get($this->values, $key));
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
            $this->assertMatchesJsonSnapshot($this->userBillingOtherItem);
        });
    }
}
