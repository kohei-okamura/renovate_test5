<?php
/**
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\Billing;

use Domain\Billing\DwsBillingStatementPdfAggregate;
use Domain\Pdf\PdfSupport;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * {@link \Domain\Billing\DwsBillingStatementPdfAggregate} のテスト.
 */
final class DwsBillingStatementPdfAggregateTest extends Test
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
            $actual = DwsBillingStatementPdfAggregate::from($this->examples->dwsBillingStatements[0]->aggregates[0]);
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
     * @return \Domain\Billing\DwsBillingStatementPdfAggregate
     */
    private function createInstance(array $attrs = []): DwsBillingStatementPdfAggregate
    {
        $x = new DwsBillingStatementPdfAggregate(
            serviceDivisionCode: '12',
            resolvedServiceDivisionCode: '重度訪問介護',
            serviceDays: '10',
            subtotalScore: '  21076',
            unitCost: '1072',
            subtotalFee: ' 225934',
            unmanagedCopay: '  22593',
            managedCopay: '  22593',
            cappedCopay: '   9300',
            adjustedCopay: '     20',
            coordinatedCopay: '   9300',
            subtotalCopay: '   9300',
            subtotalBenefit: '   9300',
            subtotalSubsidy: '     10',
        );
        return $x->copy($attrs);
    }
}
