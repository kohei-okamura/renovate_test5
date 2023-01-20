<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Billing;

use Domain\Billing\DwsBilling;
use Domain\Billing\DwsBillingBundle;
use Domain\Billing\DwsBillingStatus;
use ScalikePHP\Map;
use ScalikePHP\Seq;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\DwsBillingBundleRepositoryMixin;
use Tests\Unit\Mixins\DwsBillingServiceReportRepositoryMixin;
use Tests\Unit\Mixins\DwsBillingStatementRepositoryMixin;
use Tests\Unit\Mixins\EditDwsBillingUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;
use UseCase\Billing\ConfirmDwsBillingStatusInteractor;

/**
 * {@link \UseCase\Billing\ConfirmDwsBillingStatusInteractor} のテスト.
 */
final class ConfirmDwsBillingStatusInteractorTest extends Test
{
    use ContextMixin;
    use EditDwsBillingUseCaseMixin;
    use ExamplesConsumer;
    use DwsBillingBundleRepositoryMixin;
    use DwsBillingServiceReportRepositoryMixin;
    use DwsBillingStatementRepositoryMixin;
    use MatchesSnapshots;
    use MockeryMixin;
    use UnitSupport;

    private DwsBilling $billing;
    private DwsBillingBundle $bundle;
    private ConfirmDwsBillingStatusInteractor $interactor;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachTest(function (self $self): void {
            $self->billing = $self->examples->dwsBillings[0];
            $self->bundle = $self->examples->dwsBillingBundles[0];
        });
        self::beforeEachSpec(function (self $self): void {
            $self->dwsBillingBundleRepository
                ->allows('lookupByBillingId')
                ->andReturn(Map::from([$self->billing->id => Seq::from($self->bundle)]))
                ->byDefault();
            $self->dwsBillingServiceReportRepository
                ->allows('lookupByBundleId')
                ->andReturn(Map::from([$self->bundle->id => Seq::fromArray($self->examples->dwsBillingServiceReports)]))
                ->byDefault();
            $self->dwsBillingStatementRepository
                ->allows('lookupByBundleId')
                ->andReturn(Map::from([$self->bundle->id => Seq::fromArray($self->examples->dwsBillingStatements)]))
                ->byDefault();

            $self->interactor = app(ConfirmDwsBillingStatusInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('use EditDwsBillingUseCase with status checking when statements contain not fixed', function (): void {
            $billing = $this->billing->copy(['status' => DwsBillingStatus::ready()]);
            $statements = Seq::from(
                $this->examples->dwsBillingStatements[2]->copy(['status' => DwsBillingStatus::fixed()]),
                $this->examples->dwsBillingStatements[2]->copy(['status' => DwsBillingStatus::ready()])
            );
            $serviceReports = Seq::from(
                $this->examples->dwsBillingServiceReports[2]->copy(['status' => DwsBillingStatus::fixed()]),
                $this->examples->dwsBillingServiceReports[2]->copy(['status' => DwsBillingStatus::fixed()])
            );
            $this->dwsBillingStatementRepository
                ->expects('lookupByBundleId')
                ->with($this->bundle->id)
                ->andReturn(Map::from([$this->bundle->id => $statements]));
            $this->dwsBillingServiceReportRepository
                ->expects('lookupByBundleId')
                ->with($this->bundle->id)
                ->andReturn(Map::from([$this->bundle->id => $serviceReports]));
            $this->editDwsBillingUseCase
                ->expects('handle')
                ->with($this->context, $billing->id, equalTo(['status' => DwsBillingStatus::checking()]))
                ->andReturn($billing);

            $this->interactor->handle($this->context, $billing);
        });
        $this->should('use EditDwsBillingUseCase with status checking when service reports contain not fixed', function (): void {
            $billing = $this->billing->copy(['status' => DwsBillingStatus::ready()]);
            $statements = Seq::from(
                $this->examples->dwsBillingStatements[2]->copy(['status' => DwsBillingStatus::fixed()]),
                $this->examples->dwsBillingStatements[2]->copy(['status' => DwsBillingStatus::fixed()])
            );
            $serviceReports = Seq::from(
                $this->examples->dwsBillingServiceReports[2]->copy(['status' => DwsBillingStatus::fixed()]),
                $this->examples->dwsBillingServiceReports[2]->copy(['status' => DwsBillingStatus::ready()])
            );
            $this->dwsBillingStatementRepository
                ->expects('lookupByBundleId')
                ->with($this->bundle->id)
                ->andReturn(Map::from([$this->bundle->id => $statements]));
            $this->dwsBillingServiceReportRepository
                ->expects('lookupByBundleId')
                ->with($this->bundle->id)
                ->andReturn(Map::from([$this->bundle->id => $serviceReports]));
            $this->editDwsBillingUseCase
                ->expects('handle')
                ->with($this->context, $billing->id, equalTo(['status' => DwsBillingStatus::checking()]))
                ->andReturn($billing);

            $this->interactor->handle($this->context, $billing);
        });
        $this->should('use EditDwsBillingUseCase with status ready when all service reports and statements are fixed', function (): void {
            $billing = $this->billing->copy(['status' => DwsBillingStatus::checking()]);
            $statements = Seq::from(
                $this->examples->dwsBillingStatements[2]->copy(['status' => DwsBillingStatus::fixed()]),
                $this->examples->dwsBillingStatements[2]->copy(['status' => DwsBillingStatus::fixed()])
            );
            $serviceReports = Seq::from(
                $this->examples->dwsBillingServiceReports[2]->copy(['status' => DwsBillingStatus::fixed()]),
                $this->examples->dwsBillingServiceReports[2]->copy(['status' => DwsBillingStatus::fixed()])
            );
            $this->dwsBillingStatementRepository
                ->expects('lookupByBundleId')
                ->with($this->bundle->id)
                ->andReturn(Map::from([$this->bundle->id => $statements]));
            $this->dwsBillingServiceReportRepository
                ->expects('lookupByBundleId')
                ->with($this->bundle->id)
                ->andReturn(Map::from([$this->bundle->id => $serviceReports]));
            $this->editDwsBillingUseCase
                ->expects('handle')
                ->with($this->context, $billing->id, equalTo(['status' => DwsBillingStatus::ready()]))
                ->andReturn($billing);

            $this->interactor->handle($this->context, $billing);
        });
    }
}
