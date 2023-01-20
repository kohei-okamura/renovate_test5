<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\Billing;

use Domain\Billing\DwsBillingInvoiceItem;
use Domain\Billing\DwsBillingPaymentCategory;
use Illuminate\Support\Arr;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Test;

/**
 * {@link \Domain\Billing\DwsBillingInvoiceItem} のテスト.
 */
final class DwsBillingInvoiceItemTest extends Test
{
    use CarbonMixin;
    use ExamplesConsumer;
    use UnitSupport;
    use MatchesSnapshots;

    protected DwsBillingInvoiceItem $dwsBillingInvoiceItem;

    protected array $values = [];

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (DwsBillingInvoiceItemTest $self): void {
            $self->values = [
                'paymentCategory' => DwsBillingPaymentCategory::category1(),
                'serviceDivisionCode' => '12',
                'subtotalCount' => 10,
                'subtotalScore' => 1000,
                'subtotalFee' => 1000,
                'subtotalBenefit' => 1000,
                'subtotalCopay' => 1000,
                'subtotalSubsidy' => 1000,
                'sortOrder' => 1,
            ];
            $self->dwsBillingInvoiceItem = DwsBillingInvoiceItem::create($self->values);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_attrs(): void
    {
        $examples = [
            'paymentCategory' => ['paymentCategory'],
            'serviceDivisionCode' => ['serviceDivisionCode'],
            'subtotalCount' => ['subtotalCount'],
            'subtotalScore' => ['subtotalScore'],
            'subtotalFee' => ['subtotalFee'],
            'subtotalBenefit' => ['subtotalBenefit'],
            'subtotalCopay' => ['subtotalCopay'],
            'subtotalSubsidy' => ['subtotalSubsidy'],
        ];

        $this->should(
            'have specified attribute',
            function (string $key): void {
                $this->assertSame($this->dwsBillingInvoiceItem->get($key), Arr::get($this->values, $key));
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
            $this->assertMatchesJsonSnapshot($this->dwsBillingInvoiceItem);
        });
    }
}
