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
use Domain\ProvisionReport\LtcsProvisionReport;
use Domain\ProvisionReport\LtcsProvisionReportRepository;
use Domain\TransactionManager;
use Domain\TransactionManagerFactory;
use Lib\Exceptions\NotFoundException;
use Lib\Logging;

class DeleteLtcsProvisionReportInteractor implements DeleteLtcsProvisionReportUseCase
{
    use Logging;

    private LtcsProvisionReportRepository $repository;
    private GetLtcsProvisionReportUseCase  $getUseCase;
    private TransactionManager $transaction;

    public function __construct(
        LtcsProvisionReportRepository $repository,
        GetLtcsProvisionReportUseCase $getUseCase,
        TransactionManagerFactory $transactionManagerFactory
    ) {
        $this->repository = $repository;
        $this->getUseCase = $getUseCase;
        $this->transaction = $transactionManagerFactory->factory($repository);
    }

    /** {@inheritdoc} */
    public function handle(Context $context, int $officeId, int $userId, Carbon $providedIn): void
    {
        $provisionReport = $this->getLtcsProvisionReport($context, $officeId, $userId, $providedIn);
        $this->transaction->run(function () use ($provisionReport): void {
            $this->repository->removeById($provisionReport->id);
        });
        $this->logger()->info(
            '介護保険サービス：予実が削除されました',
            ['id' => $provisionReport->id] + $context->logContext()
        );
    }

    /**
     * 介護保険サービス：予実を取得する.
     *
     * @param \Domain\Context\Context $context
     * @param int $officeId
     * @param int $userId
     * @param \Domain\Common\Carbon $providedIn
     * @return \Domain\ProvisionReport\LtcsProvisionReport
     */
    private function getLtcsProvisionReport(Context $context, int $officeId, int $userId, Carbon $providedIn): LtcsProvisionReport
    {
        return $this->getUseCase
            ->handle($context, Permission::updateLtcsProvisionReports(), $officeId, $userId, $providedIn)
            ->getOrElse(function (): void {
                throw new NotFoundException('LtcsProvisionReport not found');
            });
    }
}
