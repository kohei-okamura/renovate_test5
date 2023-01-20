<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Billing;

use Domain\Common\Pagination;
use Domain\FinderResult;
use ScalikePHP\Seq;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\LtcsBillingInvoiceFinderMixin;
use Tests\Unit\Mixins\LtcsBillingStatementFinderMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;
use UseCase\Billing\BuildLtcsBillingInvoiceRecordListInteractor;

/**
 * {@link \UseCase\Billing\BuildLtcsBillingInvoiceRecordListUseCase} のテスト.
 */
final class BuildLtcsBillingInvoiceRecordListInteractorTest extends Test
{
    use CarbonMixin;
    use ContextMixin;
    use ExamplesConsumer;
    use LtcsBillingInvoiceFinderMixin;
    use LtcsBillingStatementFinderMixin;
    use MatchesSnapshots;
    use MockeryMixin;
    use UnitSupport;

    private BuildLtcsBillingInvoiceRecordListInteractor $interactor;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (BuildLtcsBillingInvoiceRecordListInteractorTest $self): void {
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

            $self->interactor = app(BuildLtcsBillingInvoiceRecordListInteractor::class);
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
        $this->should('return an array of ExchangeRecords', function (): void {
            // TODO: DEV-4532 バックエンドのスナップショットテスト対応
            self::markTestSkipped();

            $billing = $this->examples->ltcsBillings[0];
            $bundle = $this->examples->ltcsBillingBundles[0];

            $xs = $this->interactor->handle($this->context, $billing, $bundle);

            $this->assertMatchesModelSnapshot($xs);
        });
    }
}
