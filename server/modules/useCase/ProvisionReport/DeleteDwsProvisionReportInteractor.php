<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\ProvisionReport;

use Domain\Common\Carbon;
use Domain\Context\Context;
use Domain\Permission\Permission;
use Domain\ProvisionReport\DwsProvisionReport;
use Domain\ProvisionReport\DwsProvisionReportRepository;
use Domain\TransactionManager;
use Domain\TransactionManagerFactory;
use Lib\Exceptions\NotFoundException;
use Lib\Logging;

class DeleteDwsProvisionReportInteractor implements DeleteDwsProvisionReportUseCase
{
    use Logging;

    private DwsProvisionReportRepository $repository;
    private GetDwsProvisionReportUseCase  $getUseCase;
    private TransactionManager $transaction;

    public function __construct(
        DwsProvisionReportRepository $repository,
        GetDwsProvisionReportUseCase $getUseCase,
        TransactionManagerFactory $transactionManagerFactory
    ) {
        $this->repository = $repository;
        $this->getUseCase = $getUseCase;
        $this->transaction = $transactionManagerFactory->factory($repository);
    }

    /** {@inheritdoc} */
    public function handle(Context $context, int $officeId, int $userId, Carbon $providedIn): void
    {
        $provisionReport = $this->getDwsProvisionReport($context, $officeId, $userId, $providedIn);
        $this->transaction->run(function () use ($provisionReport): void {
            $this->repository->removeById($provisionReport->id);
        });
        $this->logger()->info(
            '障害福祉サービス：予実が削除されました',
            ['id' => $provisionReport->id] + $context->logContext()
        );
    }

    /**
     * 障害福祉サービス：予実を取得する.
     *
     * @param \Domain\Context\Context $context
     * @param int $officeId
     * @param int $userId
     * @param \Domain\Common\Carbon $providedIn
     * @return \Domain\ProvisionReport\DwsProvisionReport
     */
    private function getDwsProvisionReport(Context $context, int $officeId, int $userId, Carbon $providedIn): DwsProvisionReport
    {
        return $this->getUseCase
            ->handle($context, Permission::updateDwsProvisionReports(), $officeId, $userId, $providedIn)
            ->getOrElse(function (): void {
                throw new NotFoundException('DwsProvisionReport not found');
            });
    }
}
