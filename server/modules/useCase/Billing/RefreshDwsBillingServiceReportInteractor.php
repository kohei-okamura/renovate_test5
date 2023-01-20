<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Billing\DwsBillingBundle;
use Domain\Billing\DwsBillingServiceReport;
use Domain\Billing\DwsBillingServiceReportRepository;
use Domain\Context\Context;
use Domain\TransactionManager;
use Domain\TransactionManagerFactory;
use ScalikePHP\Seq;

/**
 * 障害福祉サービス：サービス提供実績記録票リフレッシュユースケース実装.
 */
final class RefreshDwsBillingServiceReportInteractor implements RefreshDwsBillingServiceReportUseCase
{
    private CreateDwsBillingServiceReportListUseCase $createServiceReportListUseCase;
    private DwsBillingServiceReportRepository $repository;
    private TransactionManager $transaction;

    /**
     * {@link \UseCase\Billing\RefreshLtcsBillingStatementInteractor} constructor.
     *
     * @param CreateDwsBillingServiceReportListUseCase $createServiceReportListUseCase
     * @param \Domain\Billing\DwsBillingServiceReportRepository $repository
     * @param TransactionManagerFactory $factory
     */
    public function __construct(
        CreateDwsBillingServiceReportListUseCase $createServiceReportListUseCase,
        DwsBillingServiceReportRepository $repository,
        TransactionManagerFactory $factory
    ) {
        $this->createServiceReportListUseCase = $createServiceReportListUseCase;
        $this->repository = $repository;
        $this->transaction = $factory->factory($repository);
    }

    /** {@inheritdoc} */
    public function handle(
        Context $context,
        DwsBillingBundle $bundle,
        Seq $provisionReports,
        Seq $serviceReports,
        Seq $previousProvisionReports
    ): void {
        $ids = $serviceReports->map(fn (DwsBillingServiceReport $x): int => $x->id)->toArray();
        $this->transaction->run(function () use ($previousProvisionReports, $context, $bundle, $provisionReports, $ids): void {
            // TODO
            // 簡略化のために既存のサービス提供実績記録票をすべて削除、必要なものを新規作成で処理している
            // 問題が生じたら、新規は追加、既存は更新、不要になったものは削除、に変更する
            $this->repository->removeById(...$ids);
            $this->createServiceReportListUseCase->handle(
                $context,
                $bundle,
                $provisionReports,
                $previousProvisionReports
            );
        });
    }
}
