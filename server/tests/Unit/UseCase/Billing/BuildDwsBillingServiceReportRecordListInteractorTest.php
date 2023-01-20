<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Billing;

use Domain\Billing\DwsBillingServiceReport;
use Domain\Billing\DwsBillingServiceReport as DomainDwsBillingServiceReport;
use Mockery;
use ScalikePHP\Map;
use ScalikePHP\Seq;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\DwsBillingServiceReportFinderMixin;
use Tests\Unit\Mixins\DwsBillingServiceReportRepositoryMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;
use UseCase\Billing\BuildDwsBillingServiceReportRecordListInteractor;

/**
 * {@link \UseCase\Billing\BuildDwsBillingServiceReportRecordListInteractor} のテスト.
 */
final class BuildDwsBillingServiceReportRecordListInteractorTest extends Test
{
    use CarbonMixin;
    use ContextMixin;
    use DwsBillingServiceReportFinderMixin;
    use DwsBillingServiceReportRepositoryMixin;
    use ExamplesConsumer;
    use MatchesSnapshots;
    use MockeryMixin;
    use UnitSupport;

    private BuildDwsBillingServiceReportRecordListInteractor $interactor;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (BuildDwsBillingServiceReportRecordListInteractorTest $self): void {
            $self->dwsBillingServiceReportRepository
                ->allows('lookupByBundleId')
                ->andReturn(
                    Seq::fromArray($self->examples->dwsBillingServiceReports)
                        ->groupBy(fn (DomainDwsBillingServiceReport $x): int => $x->dwsBillingBundleId)
                )
                ->byDefault();
            $self->interactor = app(BuildDwsBillingServiceReportRecordListInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('lookup DwsBillingServiceReports by bundle id', function (): void {
            $billing = $this->examples->dwsBillings[0];
            $bundles = [
                $this->examples->dwsBillingBundles[0],
                $this->examples->dwsBillingBundles[1],
            ];
            $this->dwsBillingServiceReportRepository
                ->expects('lookupByBundleId')
                ->with(Mockery::anyOf($bundles[0]->id, $bundles[1]->id))
                ->andReturnUsing(
                    fn (int $bundleId): Map => Seq::fromArray($this->examples->dwsBillingServiceReports)
                        ->filter(fn (DwsBillingServiceReport $x) => $x->dwsBillingBundleId === $bundleId)
                        ->groupBy(fn (DomainDwsBillingServiceReport $x): int => $x->dwsBillingBundleId)
                )
                ->twice();
            $this->dwsBillingServiceReportRepository->expects('lookupByBundleId')->never();

            $this->interactor->handle($this->context, $billing, Seq::fromArray($bundles));
        });

        $this->should('return an array of ExchangeRecords', function (): void {
            // TODO: DEV-4532 バックエンドのスナップショットテスト対応
            self::markTestSkipped();

            $billing = $this->examples->dwsBillings[0];
            // スキップしているのでとりあえず $bundles は適当
            $xs = $this->interactor->handle($this->context, $billing, Seq::empty());

            $this->assertMatchesModelSnapshot($xs);
        });
    }
}
