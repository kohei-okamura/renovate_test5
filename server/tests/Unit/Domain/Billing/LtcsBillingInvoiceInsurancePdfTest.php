<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\Billing;

use Domain\Billing\LtcsBillingInvoice;
use Domain\Billing\LtcsBillingInvoiceInsurancePdf;
use Domain\Common\Carbon;
use Domain\Common\DefrayerCategory;
use Illuminate\Support\Arr;
use ScalikePHP\Option;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Test;

/**
 * {@link \Domain\Billing\LtcsBillingInvoiceInsurancePdf} のテスト.
 */
final class LtcsBillingInvoiceInsurancePdfTest extends Test
{
    use CarbonMixin;
    use ExamplesConsumer;
    use UnitSupport;
    use MatchesSnapshots;

    protected LtcsBillingInvoiceInsurancePdf $ltcsBillingInvoiceInsurancePdf;

    protected array $values = [];

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->values = [
                'statementCount' => number_format(1),
                'totalScore' => number_format(1),
                'totalFee' => number_format(1),
                'insuranceAmount' => number_format(1),
                'subsidyAmount' => number_format(1),
                'copayAmount' => number_format(1),
            ];
            $self->ltcsBillingInvoiceInsurancePdf = LtcsBillingInvoiceInsurancePdf::create($self->values);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_from(): void
    {
        $invoice = new LtcsBillingInvoice(
            id: 1,
            billingId: 1,
            bundleId: 1,
            isSubsidy: false,
            defrayerCategory: DefrayerCategory::atomicBombVictim(),
            statementCount: 1,
            totalScore: 1,
            totalFee: 1,
            insuranceAmount: 1,
            subsidyAmount: 1,
            copayAmount: 1,
            createdAt: Carbon::now(),
            updatedAt: Carbon::now(),
        );
        $this->assertModelStrictEquals(
            LtcsBillingInvoiceInsurancePdf::create([
                'statementCount' => number_format(1),
                'totalScore' => number_format(1),
                'totalFee' => number_format(1),
                'insuranceAmount' => number_format(1),
                'subsidyAmount' => number_format(1),
                'copayAmount' => number_format(1),
            ]),
            LtcsBillingInvoiceInsurancePdf::from(Option::from($invoice))
        );
    }

    /**
     * @test
     * @return void
     */
    public function describe_attrs(): void
    {
        $examples = [
            'statementCount' => ['statementCount'],
            'totalScore' => ['totalScore'],
            'totalFee' => ['totalFee'],
            'insuranceAmount' => ['insuranceAmount'],
            'subsidyAmount' => ['subsidyAmount'],
            'copayAmount' => ['copayAmount'],
        ];

        $this->should(
            'have specified attribute',
            function (string $key): void {
                $this->assertSame($this->ltcsBillingInvoiceInsurancePdf->get($key), Arr::get($this->values, $key));
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
            $this->assertMatchesJsonSnapshot($this->ltcsBillingInvoiceInsurancePdf);
        });
    }
}
