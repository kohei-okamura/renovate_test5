<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\Billing;

use Domain\Billing\DwsBillingServiceReportAggregateCategory;
use Domain\Billing\DwsBillingServiceReportAggregateItem;
use Domain\Common\Decimal;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Test;

/**
 * {@link \Domain\Billing\DwsBillingServiceReportAggregateItem} のテスト.
 */
final class DwsBillingServiceReportAggregateItemTest extends Test
{
    use MatchesSnapshots;
    use UnitSupport;

    private DwsBillingServiceReportAggregateItem $item;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $key = DwsBillingServiceReportAggregateCategory::category100()->value();
            $value = Decimal::fromInt(123_45, 2);
            $self->item = DwsBillingServiceReportAggregateItem::fromAssoc([$key => $value]);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_getOption(): void
    {
        $this->should('should return total hours as Decimal', function (): void {
            $category = DwsBillingServiceReportAggregateCategory::category100();
            $expected = Decimal::fromInt(123_45, 2);

            $actual = $this->item->getOption($category)->get();

            $this->assertEquals($expected, $actual);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_jsonSerialize(): void
    {
        $this->should('be able to encode to json', function (): void {
            // 何故か自前で JSON に変換しないと動かない……
            // NOTE: コピペに用いないこと！
            $this->assertMatchesJsonSnapshot(json_encode($this->item));
        });
    }
}
