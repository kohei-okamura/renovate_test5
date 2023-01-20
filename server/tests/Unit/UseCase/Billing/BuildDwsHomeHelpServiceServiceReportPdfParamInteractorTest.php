<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Billing;

use Domain\Billing\DwsBillingServiceReportPdf;
use Domain\Common\Pagination;
use Domain\FinderResult;
use ScalikePHP\Map;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\DwsBillingServiceReportRepositoryMixin;
use Tests\Unit\Mixins\DwsBillingStatementFinderMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;
use UseCase\Billing\BuildDwsHomeHelpServiceServiceReportPdfParamInteractor;

/**
 * {@link \UseCase\Billing\BuildDwsHomeHelpServiceServiceReportPdfParamInteractor} のテスト.
 */
final class BuildDwsHomeHelpServiceServiceReportPdfParamInteractorTest extends Test
{
    use CarbonMixin;
    use ContextMixin;
    use DwsBillingServiceReportRepositoryMixin;
    use DwsBillingStatementFinderMixin;
    use ExamplesConsumer;
    use UnitSupport;
    use DwsBillingTestSupport;
    use MockeryMixin;

    private BuildDwsHomeHelpServiceServiceReportPdfParamInteractor $interactor;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachTest(function (self $self): void {
            $self->setupTestData();
        });

        self::beforeEachSpec(function (self $self): void {
            $self->dwsBillingStatementFinder
                ->allows('find')
                ->andReturn(FinderResult::from(Seq::from($self->statement), Pagination::create()))
                ->byDefault();
            $self->dwsBillingServiceReportRepository
                ->allows('lookupByBundleId')
                ->andReturn(Map::from([
                    $self->bundle->id => Seq::from($self->serviceReport),
                ]))
                ->byDefault();

            $self->interactor = app(BuildDwsHomeHelpServiceServiceReportPdfParamInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('lookup DwsBillingServiceReport by bundle id', function (): void {
            $billing = $this->billing;
            $bundle = $this->bundle;
            $bundleId = $bundle->id;
            $this->dwsBillingServiceReportRepository
                ->expects('lookupByBundleId')
                ->with($bundleId)
                ->andReturn(Map::from([
                    $this->bundle->id => Seq::from($this->serviceReport),
                ]));

            $this->interactor->handle($this->context, $billing, Seq::from($bundle));
        });

        $this->should('return an DwsBillingServiceReportPdf', function (): void {
            $billing = $this->billing;
            $bundles = Seq::from($this->bundle);
            $actual = $this->interactor->handle($this->context, $billing, $bundles)->toArray();
            $expected = DwsBillingServiceReportPdf::from(
                $this->serviceReport,
                $this->bundle->providedIn,
                $billing->office,
                Seq::fromArray($this->statement->contracts)
            )->toArray();
            $this->assertArrayStrictEquals(
                $expected,
                $actual
            );
        });
    }
}
