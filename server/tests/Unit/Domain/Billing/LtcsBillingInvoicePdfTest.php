<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\Billing;

use Domain\Billing\LtcsBillingInvoice;
use Domain\Billing\LtcsBillingInvoiceInsurancePdf;
use Domain\Billing\LtcsBillingInvoicePdf;
use Domain\Billing\LtcsBillingInvoiceSubsidyItemPdf;
use Domain\Billing\LtcsBillingInvoiceSubsidyPdf;
use Domain\Billing\LtcsBillingOffice;
use Domain\Common\Addr;
use Domain\Common\DefrayerCategory;
use Domain\Common\Prefecture;
use Illuminate\Support\Arr;
use ScalikePHP\Seq;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Test;

/**
 * {@link \Domain\Billing\LtcsBillingInvoicePdf} のテスト.
 */
final class LtcsBillingInvoicePdfTest extends Test
{
    use CarbonMixin;
    use ExamplesConsumer;
    use MatchesSnapshots;
    use UnitSupport;

    protected LtcsBillingInvoicePdf $ltcsBillingInvoicePdf;

    protected array $values = [];

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->values = [
                'office' => new LtcsBillingOffice(
                    officeId: 1,
                    code: '1176514840',
                    name: '土屋訪問介護事業所 さいたま',
                    abbr: 'さいたま',
                    addr: new Addr(
                        postcode: '164-0011',
                        prefecture: Prefecture::tokyo(),
                        city: '中野区',
                        street: '中央1-35-6',
                        apartment: 'レッチフィールド中野坂上ビル6F',
                    ),
                    tel: '050-3188-7637'
                ),
                'providedIn' => [
                    'japaneseCalender' => '',
                    'year' => '',
                    'month' => '',
                    'day' => '',
                ],
                'insurance' => LtcsBillingInvoiceInsurancePdf::create(['']),
                'subsidy' => LtcsBillingInvoiceSubsidyPdf::create(['']),
            ];
            $self->ltcsBillingInvoicePdf = LtcsBillingInvoicePdf::create($self->values);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_from(): void
    {
        $billing = $this->examples->ltcsBillings[0];
        $bundle = $this->examples->ltcsBillingBundles[0];
        $invoices = Seq::fromArray($this->examples->ltcsBillingInvoices);
        $this->assertModelStrictEquals(
            expected: LtcsBillingInvoicePdf::create([
                'office' => $billing->office,
                'providedIn' => [
                    'japaneseCalender' => mb_substr($bundle->providedIn->formatLocalized('%EC%Ey'), 0, 2),
                    'year' => mb_substr($bundle->providedIn->formatLocalized('%EC%Ey'), 2),
                    'month' => $bundle->providedIn->format('m'),
                    'day' => $bundle->providedIn->format('d'),
                ],
                'insurance' => LtcsBillingInvoiceInsurancePdf::from(
                    $invoices->filter(fn (LtcsBillingInvoice $x): bool => !$x->isSubsidy)->headOption()
                ),
                'subsidy' => LtcsBillingInvoiceSubsidyPdf::create([
                    'items' => [
                        '12' => LtcsBillingInvoiceSubsidyItemPdf::from(
                            $invoices
                                ->filter(fn (LtcsBillingInvoice $x): bool => $x->isSubsidy)
                                ->find(function (LtcsBillingInvoice $x): bool {
                                    return $x->defrayerCategory === DefrayerCategory::livelihoodProtection();
                                })
                        ),
                        '81' => LtcsBillingInvoiceSubsidyItemPdf::from(
                            $invoices
                                ->filter(fn (LtcsBillingInvoice $x): bool => $x->isSubsidy)
                                ->find(function (LtcsBillingInvoice $x): bool {
                                    return $x->defrayerCategory === DefrayerCategory::atomicBombVictim();
                                })
                        ),
                        '58' => LtcsBillingInvoiceSubsidyItemPdf::from(
                            $invoices
                                ->filter(fn (LtcsBillingInvoice $x): bool => $x->isSubsidy)
                                ->find(function (LtcsBillingInvoice $x): bool {
                                    return $x->defrayerCategory === DefrayerCategory::pwdSupport();
                                })
                        ),
                        '25' => LtcsBillingInvoiceSubsidyItemPdf::from(
                            $invoices
                                ->filter(fn (LtcsBillingInvoice $x): bool => $x->isSubsidy)
                                ->find(function (LtcsBillingInvoice $x): bool {
                                    return $x->defrayerCategory === DefrayerCategory::supportForJapaneseReturneesFromChina();
                                })
                        ),
                    ],
                    'subsidyAmountTotal' => number_format(
                        $invoices
                            ->filter(fn (LtcsBillingInvoice $x): bool => $x->isSubsidy)
                            ->map(fn (LtcsBillingInvoice $x): int => $x->subsidyAmount)->sum()
                    ),
                ]),
            ]),
            actual: LtcsBillingInvoicePdf::from(
                $billing,
                $bundle,
                $invoices
            )
        );
    }

    /**
     * @test
     * @return void
     */
    public function describe_attrs(): void
    {
        $examples = [
            'office' => ['office'],
            'providedIn' => ['providedIn'],
            'insurance' => ['insurance'],
            'subsidy' => ['subsidy'],
        ];

        $this->should(
            'have specified attribute',
            function (string $key): void {
                $this->assertSame($this->ltcsBillingInvoicePdf->get($key), Arr::get($this->values, $key));
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
            $this->assertMatchesJsonSnapshot($this->ltcsBillingInvoicePdf);
        });
    }
}
