<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\Billing;

use Domain\Billing\LtcsBillingInvoice;
use Domain\Billing\LtcsBillingInvoiceSubsidyItemPdf;
use Domain\Billing\LtcsBillingInvoiceSubsidyPdf;
use Domain\Common\DefrayerCategory;
use Illuminate\Support\Arr;
use ScalikePHP\Seq;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Test;

/**
 * {@link \Domain\Billing\LtcsBillingInvoiceSubsidyPdf} のテスト.
 */
final class LtcsBillingInvoiceSubsidyPdfTest extends Test
{
    use CarbonMixin;
    use ExamplesConsumer;
    use UnitSupport;
    use MatchesSnapshots;

    protected LtcsBillingInvoiceSubsidyPdf $ltcsBillingInvoiceSubsidyPdf;

    protected array $values = [];

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->values = [
                'items' => [
                    '12' => LtcsBillingInvoiceSubsidyItemPdf::create(['']),
                    '81' => LtcsBillingInvoiceSubsidyItemPdf::create(['']),
                    '58' => LtcsBillingInvoiceSubsidyItemPdf::create(['']),
                    '25' => LtcsBillingInvoiceSubsidyItemPdf::create(['']),
                ],
                'subsidyAmountTotal' => LtcsBillingInvoiceSubsidyItemPdf::create(['']),
            ];
            $self->ltcsBillingInvoiceSubsidyPdf = LtcsBillingInvoiceSubsidyPdf::create($self->values);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_from(): void
    {
        $this->should('', function (): void {
            $invoices = Seq::fromArray($this->examples->ltcsBillingInvoices);
            $expected = LtcsBillingInvoiceSubsidyPdf::create([
                'items' => [
                    '12' => LtcsBillingInvoiceSubsidyItemPdf::from(
                        $invoices->find(fn (LtcsBillingInvoice $x): bool => $x->defrayerCategory === DefrayerCategory::livelihoodProtection())
                    ),
                    '81' => LtcsBillingInvoiceSubsidyItemPdf::from(
                        $invoices->find(fn (LtcsBillingInvoice $x): bool => $x->defrayerCategory === DefrayerCategory::atomicBombVictim())
                    ),
                    '58' => LtcsBillingInvoiceSubsidyItemPdf::from(
                        $invoices->find(fn (LtcsBillingInvoice $x): bool => $x->defrayerCategory === DefrayerCategory::pwdSupport())
                    ),
                    '25' => LtcsBillingInvoiceSubsidyItemPdf::from(
                        $invoices->find(fn (LtcsBillingInvoice $x): bool => $x->defrayerCategory === DefrayerCategory::supportForJapaneseReturneesFromChina())
                    ),
                ],
                'subsidyAmountTotal' => number_format(
                    $invoices->map(fn (LtcsBillingInvoice $x): int => $x->subsidyAmount)->sum()
                ),
            ]);
            $this->assertModelStrictEquals(
                $expected,
                LtcsBillingInvoiceSubsidyPdf::from($invoices)
            );
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_attrs(): void
    {
        $examples = [
            'items' => ['items'],
            'subsidyAmountTotal' => ['subsidyAmountTotal'],
        ];

        $this->should(
            'have specified attribute',
            function (string $key): void {
                $this->assertSame($this->ltcsBillingInvoiceSubsidyPdf->get($key), Arr::get($this->values, $key));
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
            $this->assertMatchesJsonSnapshot($this->ltcsBillingInvoiceSubsidyPdf);
        });
    }
}
