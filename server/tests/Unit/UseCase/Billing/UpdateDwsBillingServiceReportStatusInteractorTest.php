<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Billing;

use Domain\Billing\DwsBilling;
use Domain\Billing\DwsBillingBundle;
use Domain\Billing\DwsBillingServiceReport;
use Domain\Billing\DwsBillingStatus;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ConfirmDwsBillingStatusUseCaseMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\EditDwsBillingServiceReportUseCaseMixin;
use Tests\Unit\Mixins\GetDwsBillingServiceReportInfoUseCaseMixin;
use Tests\Unit\Mixins\LoggerMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;
use UseCase\Billing\UpdateDwsBillingServiceReportStatusInteractor;

/**
 * {@link \UseCase\Billing\UpdateDwsBillingServiceReportStatusInteractor} のテスト.
 */
final class UpdateDwsBillingServiceReportStatusInteractorTest extends Test
{
    use CarbonMixin;
    use ConfirmDwsBillingStatusUseCaseMixin;
    use ContextMixin;
    use EditDwsBillingServiceReportUseCaseMixin;
    use ExamplesConsumer;
    use GetDwsBillingServiceReportInfoUseCaseMixin;
    use LoggerMixin;
    use MockeryMixin;
    use UnitSupport;

    private DwsBilling $billing;
    private DwsBillingBundle $bundle;
    private DwsBillingServiceReport $serviceReport;
    private array $info;
    private UpdateDwsBillingServiceReportStatusInteractor $interactor;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->billing = $self->examples->dwsBillings[0];
            $self->bundle = $self->examples->dwsBillingBundles[0];
            $self->serviceReport = $self->examples->dwsBillingServiceReports[0];
            $self->info = [
                'billing' => $self->billing,
                'bundles' => $self->bundle,
                'copayCoordinations' => [$self->examples->dwsBillingCopayCoordinations[0]],
                'reports' => [$self->examples->dwsBillingServiceReports[0]],
                'statements' => [$self->examples->dwsBillingServiceReports[0]],
            ];

            $self->confirmDwsBillingStatusUseCase
                ->allows('handle')
                ->andReturnNull()
                ->byDefault();
            $self->editDwsBillingServiceReportUseCase
                ->allows('handle')
                ->andReturn($self->serviceReport)
                ->byDefault();
            $self->getDwsBillingServiceReportInfoUseCase
                ->allows('handle')
                ->andReturn($self->info)
                ->byDefault();

            $self->interactor = app(UpdateDwsBillingServiceReportStatusInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('use EditDwsBillingServiceReportUseCase', function (): void {
            $this->editDwsBillingServiceReportUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    $this->serviceReport->dwsBillingId,
                    $this->serviceReport->dwsBillingBundleId,
                    $this->serviceReport->id,
                    ['status' => DwsBillingStatus::fixed()],
                )
                ->andReturn($this->examples->dwsBillingServiceReports[0]);

            $this->interactor->handle(
                $this->context,
                $this->examples->dwsBillingServiceReports[0]->dwsBillingId,
                $this->examples->dwsBillingServiceReports[0]->dwsBillingBundleId,
                $this->examples->dwsBillingServiceReports[0]->id,
                ['status' => DwsBillingStatus::fixed()]
            );
        });
        $this->should('use GetDwsBillingServiceReportInfoUseCase', function (): void {
            $this->getDwsBillingServiceReportInfoUseCase
                ->expects('handle')
                ->andReturn($this->info);

            $this->interactor->handle(
                $this->context,
                $this->examples->dwsBillingServiceReports[0]->dwsBillingId,
                $this->examples->dwsBillingServiceReports[0]->dwsBillingBundleId,
                $this->examples->dwsBillingServiceReports[0]->id,
                ['status' => DwsBillingStatus::fixed()]
            );
        });
        $this->should('return array of ServiceReportInfo', function (): void {
            $this->getDwsBillingServiceReportInfoUseCase
                ->expects('handle')
                ->andReturn($this->info);

            $this->assertSame(
                $this->interactor->handle(
                    $this->context,
                    $this->examples->dwsBillingServiceReports[0]->dwsBillingId,
                    $this->examples->dwsBillingServiceReports[0]->dwsBillingBundleId,
                    $this->examples->dwsBillingServiceReports[0]->id,
                    ['status' => DwsBillingStatus::fixed()]
                ),
                $this->info
            );
        });
        $this->should('use ConfirmDwsBillingStatusUseCase', function (): void {
            $this->confirmDwsBillingStatusUseCase
                ->expects('handle')
                ->with($this->context, equalTo($this->info['billing']))
                ->andReturn();

            $this->interactor->handle(
                $this->context,
                $this->examples->dwsBillingServiceReports[0]->dwsBillingId,
                $this->examples->dwsBillingServiceReports[0]->dwsBillingBundleId,
                $this->examples->dwsBillingServiceReports[0]->id,
                ['status' => DwsBillingStatus::fixed()]
            );
        });
    }
}
