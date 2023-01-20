<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Billing\DwsBillingBundle;
use Domain\Billing\DwsBillingServiceReport;
use Domain\Billing\DwsBillingServiceReportRepository;
use Domain\Context\Context;
use Domain\ProvisionReport\DwsProvisionReport;
use Domain\TransactionManager;
use Domain\TransactionManagerFactory;
use ScalikePHP\Map;
use ScalikePHP\Seq;

/**
 * 障害福祉サービス：サービス提供実績記録票生成ユースケース実装.
 */
final class CreateDwsBillingServiceReportListInteractor implements CreateDwsBillingServiceReportListUseCase
{
    private BuildDwsBillingServiceReportListUseCase $buildUseCase;
    private DwsBillingServiceReportRepository $repository;
    private TransactionManager $transaction;

    /**
     * {@link \UseCase\Billing\CreateDwsBillingServiceReportListInteractor} Constructor.
     *
     * @param \UseCase\Billing\BuildDwsBillingServiceReportListUseCase $buildUseCase
     * @param \Domain\Billing\DwsBillingServiceReportRepository $repository
     * @param \Domain\TransactionManagerFactory $factory
     */
    public function __construct(
        BuildDwsBillingServiceReportListUseCase $buildUseCase,
        DwsBillingServiceReportRepository $repository,
        TransactionManagerFactory $factory
    ) {
        $this->buildUseCase = $buildUseCase;
        $this->repository = $repository;
        $this->transaction = $factory->factory($repository);
    }

    /** {@inheritdoc} */
    public function handle(Context $context, DwsBillingBundle $bundle, Seq $provisionReports, Seq $previousProvisionReports): Seq
    {
        $previousProvisionReportsByUserId = $previousProvisionReports->toMap(fn (DwsProvisionReport $x): int => $x->userId);
        return $this->transaction->run(
            fn (): Seq => $this->create(
                $context,
                $bundle,
                $provisionReports,
                $previousProvisionReportsByUserId
            )->computed()
        );
    }

    /**
     * 障害福祉サービス：サービス提供実績記録票の一覧を生成する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Billing\DwsBillingBundle $bundle
     * @param \Domain\ProvisionReport\DwsProvisionReport[]|\ScalikePHP\Seq $provisionReports
     * @param \Domain\ProvisionReport\DwsProvisionReport[]|\ScalikePHP\Map $previousProvisionReportsByUserId
     * @return \Domain\Billing\DwsBillingServiceReport[]|\ScalikePHP\Seq
     */
    private function create(Context $context, DwsBillingBundle $bundle, Seq $provisionReports, Map $previousProvisionReportsByUserId): Seq
    {
        return $provisionReports
            ->flatMap(fn (DwsProvisionReport $x): Seq => $this->buildUseCase->handle(
                $context,
                $bundle,
                $x,
                $previousProvisionReportsByUserId->get($x->userId)
            ))
            ->map(fn (DwsBillingServiceReport $x): DwsBillingServiceReport => $this->repository->store($x));
    }
}
