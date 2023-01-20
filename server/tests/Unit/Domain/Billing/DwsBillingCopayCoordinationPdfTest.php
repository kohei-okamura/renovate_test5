<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\Billing;

use Domain\Billing\CopayCoordinationResult;
use Domain\Billing\DwsBillingCopayCoordinationItem;
use Domain\Billing\DwsBillingCopayCoordinationPayment;
use Domain\Billing\DwsBillingCopayCoordinationPdf;
use Domain\Billing\DwsBillingCopayCoordinationPdfItem;
use Domain\Billing\DwsBillingOffice;
use Domain\Billing\DwsBillingUser;
use ScalikePHP\Seq;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Test;

/**
 * {@link \Domain\Billing\DwsBillingCopayCoordinationPdf} のテスト.
 */
final class DwsBillingCopayCoordinationPdfTest extends Test
{
    use CarbonMixin;
    use ExamplesConsumer;
    use UnitSupport;
    use MatchesSnapshots;

    protected DwsBillingCopayCoordinationPdf $dwsBillingCopayCoordinationPdf;

    protected array $values = [];
    protected array $dwsPaymentValues = [];
    protected array $highCostDwsPaymentValues = [];
    protected Seq $itemValues;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (DwsBillingCopayCoordinationPdfTest $self): void {
            $self->itemValues = Seq::from(
                new DwsBillingCopayCoordinationPdfItem(
                    itemNumber: 1,
                    officeCode: '123456',
                    officeName: '事業所1',
                    fee: 10000,
                    copay: 10000,
                    coordinatedCopay: 10000,
                )
            );
            $self->values = [
                'providedIn' => [
                    'japaneseCalender' => '',
                    'year' => '',
                    'month' => '',
                    'day' => '',
                ],
                'office' => DwsBillingOffice::create([]),
                'cityCode' => '123456',
                'user' => DwsBillingUser::create([]),
                'result' => CopayCoordinationResult::coordinated(),
                'items' => $self->itemValues,
                'total' => DwsBillingCopayCoordinationPayment::create([]),
            ];
            $self->dwsBillingCopayCoordinationPdf = new DwsBillingCopayCoordinationPdf(
                providedIn: $self->values['providedIn'],
                office: $self->values['office'],
                cityCode: $self->values['cityCode'],
                user: $self->values['user'],
                result: $self->values['result'],
                items: $self->values['items'],
                total: $self->values['total'],
            );
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_from(): void
    {
        $bundle = $this->examples->dwsBillingBundles[0];
        $copayCoordination = $this->examples->dwsBillingCopayCoordinations[0];
        $this->assertModelStrictEquals(
            new DwsBillingCopayCoordinationPdf(
                providedIn : [
                    'japaneseCalender' => mb_substr($bundle->providedIn->toJapaneseYearWithEra(), 0, 2),
                    'year' => mb_substr($bundle->providedIn->toJapaneseYearWithEra(), 2),
                    'month' => $bundle->providedIn->format('m'),
                    'day' => $bundle->providedIn->format('d'),
                ],
                office: $copayCoordination->office,
                cityCode: $bundle->cityCode,
                user: $copayCoordination->user,
                result: $copayCoordination->result,
                items: Seq::fromArray($copayCoordination->items)
                    ->map(
                        fn (DwsBillingCopayCoordinationItem $x): DwsBillingCopayCoordinationPdfItem => new DwsBillingCopayCoordinationPdfItem(
                            itemNumber: $x->itemNumber,
                            officeCode: $x->office->code,
                            officeName: $x->office->name,
                            fee: $x->subtotal->fee,
                            copay: $x->subtotal->copay,
                            coordinatedCopay: $x->subtotal->coordinatedCopay
                        )
                    ),
                total: $copayCoordination->total,
            ),
            DwsBillingCopayCoordinationPdf::from(
                $bundle,
                $copayCoordination
            )
        );
    }

    /**
     * @test
     * @return void
     */
    public function describe_json(): void
    {
        $this->should('changes to JSON encode results', function (): void {
            $this->assertMatchesModelSnapshot($this->dwsBillingCopayCoordinationPdf);
        });
    }
}
