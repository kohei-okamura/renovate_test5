<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Billing;

use Domain\Billing\DwsBilling;
use Domain\Billing\DwsBillingBundle;
use Domain\Billing\DwsBillingCopayCoordination;
use Domain\Billing\DwsBillingServiceReport;
use Domain\Billing\DwsBillingStatement;
use Domain\Common\Pagination;
use Domain\FinderResult;
use Domain\Model;
use Domain\Permission\Permission;
use Lib\Exceptions\NotFoundException;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\DwsBillingBundleFinderMixin;
use Tests\Unit\Mixins\DwsBillingCopayCoordinationFinderMixin;
use Tests\Unit\Mixins\DwsBillingServiceReportFinderMixin;
use Tests\Unit\Mixins\DwsBillingStatementFinderMixin;
use Tests\Unit\Mixins\LookupDwsBillingUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;
use UseCase\Billing\GetDwsBillingInfoInteractor;

/**
 * {@link \UseCase\Billing\GetDwsBillingInfoInteractor} Test.
 */
class GetDwsBillingInfoInteractorTest extends Test
{
    use ContextMixin;
    use DwsBillingBundleFinderMixin;
    use DwsBillingCopayCoordinationFinderMixin;
    use DwsBillingServiceReportFinderMixin;
    use DwsBillingStatementFinderMixin;
    use ExamplesConsumer;
    use LookupDwsBillingUseCaseMixin;
    use MockeryMixin;
    use UnitSupport;

    private DwsBilling $billing;
    private DwsBillingBundle $bundle;
    private DwsBillingCopayCoordination $copayCoordination;
    private DwsBillingServiceReport $serviceReport;
    private DwsBillingStatement $statement;

    private GetDwsBillingInfoInteractor $interactor;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (GetDwsBillingInfoInteractorTest $self): void {
            $self->billing = $self->examples->dwsBillings[0];
            $self->bundle = $self->examples->dwsBillingBundles[0];
            $self->copayCoordination = $self->examples->dwsBillingCopayCoordinations[0];
            $self->serviceReport = $self->examples->dwsBillingServiceReports[0];
            $self->statement = $self->examples->dwsBillingStatements[0];

            $self->lookupDwsBillingUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->billing))
                ->byDefault();

            $self->dwsBillingBundleFinder
                ->allows('find')
                ->andReturn($self->buildFinderResult($self->bundle))
                ->byDefault();
            $self->dwsBillingCopayCoordinationFinder
                ->allows('find')
                ->andReturn($self->buildFinderResult($self->copayCoordination))
                ->byDefault();
            $self->dwsBillingServiceReportFinder
                ->allows('find')
                ->andReturn($self->buildFinderResult($self->serviceReport))
                ->byDefault();
            $self->dwsBillingStatementFinder
                ->allows('find')
                ->andReturn($self->buildFinderResult($self->statement))
                ->byDefault();

            $self->interactor = app(GetDwsBillingInfoInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('return assoc with parameters', function (): void {
            $actual = $this->interactor->handle($this->context, $this->billing->id);

            $this->assertArrayHasKey('billing', $actual);
            $this->assertArrayHasKey('bundles', $actual);
            $this->assertArrayHasKey('copayCoordinations', $actual);
            $this->assertArrayHasKey('reports', $actual);
            $this->assertArrayHasKey('statements', $actual);

            $this->assertModelStrictEquals($this->billing, $actual['billing']);
            $this->assertArrayStrictEquals([$this->bundle], $actual['bundles']);
            $this->assertArrayStrictEquals([$this->copayCoordination], $actual['copayCoordinations']);
            $this->assertArrayStrictEquals([$this->serviceReport], $actual['reports']);
            $this->assertArrayStrictEquals([$this->statement], $actual['statements']);
        });
        $this->should('use LookupDwsBillingUseCase', function (): void {
            $this->lookupDwsBillingUseCase
                ->expects('handle')
                ->with($this->context, Permission::viewBillings(), $this->bundle->id)
                ->andReturn(Seq::from($this->billing));

            $this->interactor->handle($this->context, $this->billing->id);
        });
        $this->should('use DwsBillingBundleFinder', function (): void {
            $this->dwsBillingBundleFinder
                ->expects('find')
                ->with(['dwsBillingId' => $this->billing->id], ['all' => true, 'sortBy' => 'id'])
                ->andReturn($this->buildFinderResult($this->bundle));

            $this->interactor->handle($this->context, $this->billing->id);
        });
        $this->should('use DwsBillingCopayCoordinationFinder', function (): void {
            $this->dwsBillingCopayCoordinationFinder
                ->expects('find')
                ->with(['dwsBillingBundleIds' => [$this->bundle->id]], ['all' => true, 'sortBy' => 'id'])
                ->andReturn($this->buildFinderResult($this->copayCoordination));

            $this->interactor->handle($this->context, $this->billing->id);
        });
        $this->should('use DwsBillingServiceReportFinder', function (): void {
            $this->dwsBillingServiceReportFinder
                ->expects('find')
                ->with(['dwsBillingBundleIds' => [$this->bundle->id]], ['all' => true, 'sortBy' => 'id'])
                ->andReturn($this->buildFinderResult($this->serviceReport));

            $this->interactor->handle($this->context, $this->billing->id);
        });
        $this->should('use DwsBillingBundleStatementFinder', function (): void {
            $this->dwsBillingStatementFinder
                ->expects('find')
                ->with(['dwsBillingBundleIds' => [$this->bundle->id]], ['all' => true, 'sortBy' => 'id'])
                ->andReturn($this->buildFinderResult($this->statement));

            $this->interactor->handle($this->context, $this->billing->id);
        });
        $this->should('throw NotFoundException when LookupDwsBillingUseCase return empty', function (): void {
            $this->lookupDwsBillingUseCase
                ->allows('handle')
                ->andReturn(Seq::emptySeq());

            $this->assertThrows(
                NotFoundException::class,
                function (): void {
                    $this->interactor->handle($this->context, $this->billing->id);
                }
            );
        });
    }

    /**
     * FinderResult を組み立てる.
     *
     * @param \Domain\Model ...$models
     * @return \Domain\FinderResult
     */
    protected function buildFinderResult(Model ...$models): FinderResult
    {
        return FinderResult::from(Seq::from(...$models), Pagination::create());
    }
}
