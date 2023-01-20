<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Billing;

use App\Concretes\PermanentDatabaseTransactionManager;
use Domain\Billing\DwsBillingBundle;
use Domain\Billing\DwsBillingServiceReport;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\CreateDwsBillingServiceReportListUseCaseMixin;
use Tests\Unit\Mixins\DwsBillingServiceReportRepositoryMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\TransactionManagerMixin;
use Tests\Unit\Test;
use UseCase\Billing\RefreshDwsBillingServiceReportInteractor;

/**
 * {@link \UseCase\Billing\RefreshDwsBillingServiceReportInteractor} のテスト.
 */
final class RefreshDwsBillingServiceReportInteractorTest extends Test
{
    use ContextMixin;
    use CreateDwsBillingServiceReportListUseCaseMixin;
    use DwsBillingServiceReportRepositoryMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use TransactionManagerMixin;
    use UnitSupport;

    protected DwsBillingBundle $billingBundle;
    /** @var \Domain\ProvisionReport\DwsProvisionReport[]&\ScalikePHP\Seq */
    protected Seq $provisionReports;
    /** @var \Domain\ProvisionReport\DwsProvisionReport[]&\ScalikePHP\Seq */
    protected Seq $previousProvisionReports;
    /** @var \Domain\Billing\DwsBillingServiceReport[]&\ScalikePHP\Seq */
    protected Seq $serviceReports;
    private RefreshDwsBillingServiceReportInteractor $interactor;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->billingBundle = $self->examples->dwsBillingBundles[0];
            $self->provisionReports = $self->createProvisionReports();
            $self->previousProvisionReports = $self->createPreviousProvisionReports();
            $self->serviceReports = $self->createServiceReports();
            $self->createDwsBillingServiceReportListUseCase
                ->allows('handle')
                ->andReturn($self->serviceReports)
                ->byDefault();
            $self->dwsBillingServiceReportRepository
                ->allows('removeById')
                ->byDefault();
            $self->dwsBillingServiceReportRepository
                ->allows('transactionManager')
                ->andReturn(PermanentDatabaseTransactionManager::class)
                ->passthru();

            $self->interactor = app(RefreshDwsBillingServiceReportInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('run in transaction', function (): void {
            $this->transactionManager->expects('run');
            $this->createDwsBillingServiceReportListUseCase->expects('handle')->never();
            $this->dwsBillingServiceReportRepository->expects('removeById')->never();

            $this->interactor->handle(
                $this->context,
                $this->billingBundle,
                $this->provisionReports,
                $this->serviceReports,
                $this->previousProvisionReports
            );
        });

        $this->should('use DwsBillingServiceReportRepository', function (): void {
            $ids = $this->serviceReports->map(fn (DwsBillingServiceReport $x): int => $x->id)->toArray();
            $this->dwsBillingServiceReportRepository
                ->expects('removeById')
                ->with(...$ids);

            $this->interactor->handle(
                $this->context,
                $this->billingBundle,
                $this->provisionReports,
                $this->serviceReports,
                $this->previousProvisionReports
            );
        });

        $this->should('use CreateDwsBillingServiceReportListUseCase', function (): void {
            $this->createDwsBillingServiceReportListUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    $this->billingBundle,
                    $this->provisionReports,
                    $this->previousProvisionReports
                );

            $this->interactor->handle(
                $this->context,
                $this->billingBundle,
                $this->provisionReports,
                $this->serviceReports,
                $this->previousProvisionReports
            );
        });
    }

    /**
     * リフレッシュに使用する予実を作る
     *
     * @return \Domain\ProvisionReport\DwsProvisionReport[]&\ScalikePHP\Seq
     */
    private function createProvisionReports(): Seq
    {
        return Seq::from(
            $this->examples->dwsProvisionReports[0],
            $this->examples->dwsProvisionReports[1],
            $this->examples->dwsProvisionReports[2]
        );
    }

    /**
     * リフレッシュに使用する前月の予実を作る
     *
     * @return \Domain\ProvisionReport\DwsProvisionReport[]&\ScalikePHP\Seq
     */
    private function createPreviousProvisionReports(): Seq
    {
        $provisionReports = $this->examples->dwsProvisionReports;
        return Seq::from(
            $provisionReports[0]->copy([
                'providedIn' => $provisionReports[0]->providedIn->subMonth(),
            ]),
            $provisionReports[1]->copy([
                'providedIn' => $provisionReports[1]->providedIn->subMonth(),
            ]),
            $provisionReports[2]->copy([
                'providedIn' => $provisionReports[2]->providedIn->subMonth(),
            ]),
        );
    }

    /**
     * リフレッシュ対象のサービス提供実績記録票一覧を作る
     *
     * @return \Domain\Billing\DwsBillingServiceReport[]&\ScalikePHP\Seq
     */
    private function createServiceReports(): Seq
    {
        return Seq::from(
            $this->examples->dwsBillingServiceReports[0],
            $this->examples->dwsBillingServiceReports[1],
            $this->examples->dwsBillingServiceReports[2]
        );
    }
}
