<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Billing;

use Domain\Billing\LtcsBillingBundle;
use Domain\Billing\LtcsBillingInvoicePdf;
use Domain\Billing\LtcsBillingOffice;
use Domain\Billing\LtcsBillingStatement;
use Domain\Billing\LtcsBillingStatementItem;
use Domain\Billing\LtcsBillingStatementPdf;
use Domain\Common\Pagination;
use Domain\FinderResult;
use Domain\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionaryEntry;
use ScalikePHP\Map;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\LtcsBillingInvoiceFinderMixin;
use Tests\Unit\Mixins\LtcsBillingStatementFinderMixin;
use Tests\Unit\Mixins\LtcsHomeVisitLongTermCareDictionaryEntryFinderMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;
use UseCase\Billing\BuildLtcsBillingInvoicePdfParamInteractor;

/**
 * {@link \UseCase\Billing\BuildLtcsBillingInvoicePdfParamInteractor} のテスト.
 */
final class BuildLtcsBillingInvoicePdfParamInteractorTest extends Test
{
    use CarbonMixin;
    use ContextMixin;
    use ExamplesConsumer;
    use LtcsBillingInvoiceFinderMixin;
    use LtcsBillingStatementFinderMixin;
    use LtcsHomeVisitLongTermCareDictionaryEntryFinderMixin;
    use MockeryMixin;
    use UnitSupport;

    private BuildLtcsBillingInvoicePdfParamInteractor $interactor;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (BuildLtcsBillingInvoicePdfParamInteractorTest $self): void {
            $self->ltcsBillingInvoiceFinder
                ->allows('find')
                ->andReturn(FinderResult::from(
                    Seq::fromArray($self->examples->ltcsBillingInvoices),
                    Pagination::create(),
                ))
                ->byDefault();
            $self->ltcsBillingStatementFinder
                ->allows('find')
                ->andReturn(FinderResult::from(
                    Seq::fromArray($self->examples->ltcsBillingStatements),
                    Pagination::create(),
                ))
                ->byDefault();
            $self->ltcsHomeVisitLongTermCareDictionaryEntryFinder
                ->allows('find')
                ->andReturn(FinderResult::from(
                    Seq::fromArray($self->examples->ltcsHomeVisitLongTermCareDictionaryEntries),
                    Pagination::create()
                ))
                ->byDefault();

            $self->interactor = app(BuildLtcsBillingInvoicePdfParamInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('find LtcsBillingInvoice by billing id and bundle id', function (): void {
            $billing = $this->examples->ltcsBillings[0];
            $billingId = $billing->id;
            $bundle = $this->examples->ltcsBillingBundles[0];
            $bundleId = $bundle->id;
            $this->ltcsBillingInvoiceFinder
                ->expects('find')
                ->with(compact('billingId', 'bundleId'), ['all' => true, 'sortBy' => 'id'])
                ->andReturn(FinderResult::from(
                    Seq::fromArray($this->examples->ltcsBillingInvoices),
                    Pagination::create(),
                ));

            $this->interactor->handle($this->context, $billing, $bundle);
        });
        $this->should('find LtcsBillingStatement by billing id and bundle id', function (): void {
            $billing = $this->examples->ltcsBillings[0];
            $billingId = $billing->id;
            $bundle = $this->examples->ltcsBillingBundles[0];
            $bundleId = $bundle->id;
            $this->ltcsBillingStatementFinder
                ->expects('find')
                ->with(compact('billingId', 'bundleId'), ['all' => true, 'sortBy' => 'id'])
                ->andReturn(FinderResult::from(
                    Seq::fromArray($this->examples->ltcsBillingStatements),
                    Pagination::create(),
                ));

            $this->interactor->handle($this->context, $billing, $bundle);
        });
        $this->should('return an array of params for ltcs invoice pdf', function (): void {
            $billing = $this->examples->ltcsBillings[0];
            $bundle = $this->examples->ltcsBillingBundles[0];
            $expected = [
                'invoice' => LtcsBillingInvoicePdf::from($billing, $bundle, Seq::fromArray($this->examples->ltcsBillingInvoices)),
                'statements' => $this->createStatementPdfs($billing->office, $bundle, Seq::fromArray($this->examples->ltcsBillingStatements)),
            ];

            $actual = $this->interactor->handle($this->context, $billing, $bundle);
            $this->assertEquals(
                $expected,
                $actual
            );
            $this->assertModelStrictEquals($expected['invoice'], $actual['invoice']);
            $this->assertArrayStrictEquals($expected['statements']->toArray(), $actual['statements']->toArray());
        });
        $this->specify('対象の請求単位のサービス提供年月でサービスコード辞書エントリを取得する', function (): void {
            $billing = $this->examples->ltcsBillings[0];
            $bundle = $this->examples->ltcsBillingBundles[0];
            $paginationParams = ['all' => true, 'sortBy' => 'id', 'desc' => true];
            // 明細書に含まれているサービスコードの配列
            $serviceCodes = Seq::fromArray($this->examples->ltcsBillingStatements[0]->items)
                ->map(fn (LtcsBillingStatementItem $x): string => $x->serviceCode->toString())
                ->toArray();
            $providedIn = $bundle->providedIn;

            $this->ltcsBillingStatementFinder
                ->allows('find')
                ->andReturn(FinderResult::from(
                    Seq::from($this->examples->ltcsBillingStatements[0]),
                    Pagination::create(),
                ))
                ->byDefault();
            $this->ltcsHomeVisitLongTermCareDictionaryEntryFinder
                ->expects('find')
                ->with(['serviceCodes' => $serviceCodes, 'providedIn' => $providedIn], $paginationParams)
                ->andReturn(FinderResult::from(
                    Seq::fromArray($this->examples->ltcsHomeVisitLongTermCareDictionaryEntries),
                    Pagination::create()
                ));

            $this->interactor->handle($this->context, $billing, $bundle);
        });
    }

    /**
     * 介護保険サービス：明細書 PDF ドメインを生成する.
     *
     * @param \Domain\Billing\LtcsBillingOffice $office
     * @param \Domain\Billing\LtcsBillingStatement[]|\ScalikePHP\Seq $statements
     * @param \Domain\Billing\LtcsBillingBundle $bundle
     * @return \Domain\Billing\LtcsBillingStatementPdf[]|\ScalikePHP\Seq
     */
    private function createStatementPdfs(LtcsBillingOffice $office, LtcsBillingBundle $bundle, Seq $statements): Seq
    {
        $serviceCodeMap = $this->getServiceCodeMap($statements);
        return $statements->map(
            function (LtcsBillingStatement $x) use ($office, $bundle, $serviceCodeMap): LtcsBillingStatementPdf {
                return LtcsBillingStatementPdf::from($office, $bundle, $x, $serviceCodeMap);
            }
        );
    }

    /**
     * サービスコード => 辞書エントリ の Map を生成する.
     *
     * @param \Domain\Billing\LtcsBillingStatement[]|\ScalikePHP\Seq $statements
     * @return \ScalikePHP\Map
     */
    private function getServiceCodeMap(Seq $statements): Map
    {
        $entries = FinderResult::from(
            Seq::fromArray($this->examples->ltcsHomeVisitLongTermCareDictionaryEntries),
            Pagination::create()
        )
            ->list;

        return $entries
            ->groupBy(fn (LtcsHomeVisitLongTermCareDictionaryEntry $x): string => $x->serviceCode->toString())
            ->mapValues(fn (Seq $x): string => $x->head()->name);
    }
}
