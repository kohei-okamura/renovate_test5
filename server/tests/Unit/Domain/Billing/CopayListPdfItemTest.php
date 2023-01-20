<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\Billing;

use Domain\Billing\CopayListPdfItem;
use Domain\Billing\DwsBillingBundle;
use Domain\Billing\DwsBillingStatement;
use Domain\Billing\DwsServiceDivisionCode;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * {@link \Domain\Billing\CopayListPdfItem} のテスト.
 */
final class CopayListPdfItemTest extends Test
{
    use CarbonMixin;
    use ExamplesConsumer;
    use MatchesSnapshots;
    use MockeryMixin;
    use UnitSupport;

    private DwsBillingBundle $bundle;
    private DwsBillingStatement $statement;
    private int $index;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (CopayListPdfItemTest $self): void {
            $self->bundle = $self->examples->dwsBillingBundles[0];
            $user2 = $self->examples->dwsBillingStatements[4]->user->copy([
                'dwsNumber' => '5432106789',
            ]);
            $self->statement = $self->examples->dwsBillingStatements[4]->copy([
                'user' => $user2,
                'totalAdjustedCopay' => null,
                'totalFee' => 150000,
                'totalCappedCopay' => 37200,
            ]);
            $self->index = 5;
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_construct(): void
    {
        $this->should('create CopayListPdfItem', function (): void {
            $actual = new CopayListPdfItem(
                itemNumber: 1,
                cityCode: '123456',
                dwsNumber: '1234567890',
                name: '利用者のなまえ',
                fee: 37200,
                copay: 37200,
                serviceDivision: [DwsServiceDivisionCode::homeHelpService(), DwsServiceDivisionCode::visitingCareForPwsd()]
            );
            $this->assertMatchesModelSnapshot($actual);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_from(): void
    {
        $this->should('return CopayListPdfItem', function (): void {
            $actual = CopayListPdfItem::from($this->bundle, $this->statement, $this->index);
            $this->assertMatchesModelSnapshot($actual);
        });
        $this->should('set totalCappedCopay to copay when totalCappedCopay is less than totalAdjustedCopay', function (): void {
            $totalCappedCopay = 30;
            $totalAdjustedCopay = 100;
            $statement = $this->statement->copy([
                'totalCappedCopay' => $totalCappedCopay,
                'totalAdjustedCopay' => $totalAdjustedCopay,
            ]);

            $actual = CopayListPdfItem::from($this->bundle, $statement, $this->index);
            $this->assertSame($totalCappedCopay, $actual->copay);
        });
        $this->should('set totalAdjustedCopay to copay when totalAdjustedCopay is less than totalCappedCopay', function (): void {
            $totalCappedCopay = 100;
            $totalAdjustedCopay = 30;
            $statement = $this->statement->copy([
                'totalCappedCopay' => $totalCappedCopay,
                'totalAdjustedCopay' => $totalAdjustedCopay,
            ]);

            $actual = CopayListPdfItem::from($this->bundle, $statement, $this->index);
            $this->assertSame($totalAdjustedCopay, $actual->copay);
        });
    }
}
