<?php
/**
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\Billing;

use Domain\Billing\DwsBillingStatementPdfItem;
use Domain\Pdf\PdfSupport;
use ScalikePHP\Map;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * {@link \Domain\Billing\DwsBillingStatementPdfItem} のテスト.
 */
final class DwsBillingStatementPdfItemTest extends Test
{
    use ExamplesConsumer;
    use MatchesSnapshots;
    use MockeryMixin;
    use PdfSupport;
    use UnitSupport;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
    }

    /**
     * @test
     * @return void
     */
    public function describe_from(): void
    {
        $this->should('return an instance', function (): void {
            $actual = DwsBillingStatementPdfItem::from(
                $this->examples->dwsBillingStatements[0]->items[0],
                Map::from([
                    '111111' => 'テスト用サービス',
                ])
            );
            $this->assertMatchesModelSnapshot($actual);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_json(): void
    {
        $this->should('changes to JSON encode results', function (): void {
            $this->assertMatchesModelSnapshot($this->createInstance());
        });
    }

    /**
     * テスト対象のインスタンスを生成する.
     *
     * @param array $attrs
     * @return \Domain\Billing\DwsBillingStatementPdfItem
     */
    private function createInstance(array $attrs = []): DwsBillingStatementPdfItem
    {
        $x = new DwsBillingStatementPdfItem(
            serviceName: '重訪Ⅰ日中1.0',
            serviceCode: '121171',
            unitScore: '213',
            count: '1',
            totalScore: '213',
        );
        return $x->copy($attrs);
    }
}
