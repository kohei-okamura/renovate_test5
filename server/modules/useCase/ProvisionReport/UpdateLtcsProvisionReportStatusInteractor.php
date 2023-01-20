<?php
/**
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
use Domain\ProvisionReport\LtcsProvisionReportStatus;
use Domain\TransactionManager;
use Domain\TransactionManagerFactory;
use Lib\Exceptions\NotFoundException;
use Lib\Logging;

/**
 * 介護保険サービス：予実 状態更新ユースケース実装.
 */
class UpdateLtcsProvisionReportStatusInteractor implements UpdateLtcsProvisionReportStatusUseCase
{
    use Logging;

    private GetLtcsProvisionReportUseCase $getLtcsProvisionReportUseCase;
    private LtcsProvisionReportRepository $repository;
    private TransactionManager $transaction;

    /**
     * Constructor.
     *
     * @param \UseCase\ProvisionReport\GetLtcsProvisionReportUseCase $getLtcsProvisionReportUseCase
     * @param \Domain\ProvisionReport\LtcsProvisionReportRepository $repository
     * @param \Domain\TransactionManagerFactory $factory
     */
    public function __construct(
        GetLtcsProvisionReportUseCase $getLtcsProvisionReportUseCase,
        LtcsProvisionReportRepository $repository,
        TransactionManagerFactory $factory
    ) {
        $this->getLtcsProvisionReportUseCase = $getLtcsProvisionReportUseCase;
        $this->repository = $repository;
        $this->transaction = $factory->factory($repository);
    }

    /** {@inheritdoc} */
    public function handle(
        Context $context,
        int $officeId,
        int $userId,
        string $providedIn,
        array $values
    ): LtcsProvisionReport {
        $ltcsProvisionReport = $this->ltcsProvisionReport($context, $officeId, $userId, $providedIn);
        return $this->editLtcsProvisionReport($context, $ltcsProvisionReport, $values);
    }

    /**
     * 介護保険サービス：予実を取得する.
     *
     * @param \Domain\Context\Context $context
     * @param int $officeId
     * @param int $userId
     * @param string $providedIn
     * @return \Domain\ProvisionReport\LtcsProvisionReport
     */
    private function ltcsProvisionReport(Context $context, int $officeId, int $userId, string $providedIn): LtcsProvisionReport
    {
        return $this->getLtcsProvisionReportUseCase
            ->handle(
                $context,
                Permission::updateLtcsProvisionReports(),
                $officeId,
                $userId,
                Carbon::parse($providedIn)
            )->getOrElse(function () use ($officeId, $userId, $providedIn) {
                throw new NotFoundException(
                    "LtcsProvisionReport(officeId: {$officeId}, userId: {$userId}, providedIn: {$providedIn}) not found."
                );
            });
    }

    /**
     * 介護保険サービス：予実を編集する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\ProvisionReport\LtcsProvisionReport $provisionReport
     * @param array $values
     * @return \Domain\ProvisionReport\LtcsProvisionReport
     */
    private function editLtcsProvisionReport(Context $context, LtcsProvisionReport $provisionReport, array $values): LtcsProvisionReport
    {
        $x = $this->transaction->run(fn (): LtcsProvisionReport => $this->repository->store(
            $provisionReport->copy(
                [
                    'fixedAt' => $values['status'] === LtcsProvisionReportStatus::fixed()
                        ? Carbon::now()
                        : null,
                    'updatedAt' => Carbon::now(),
                ] + $values
            )
        ));
        $this->logger()->info(
            '介護保険サービス：予実が更新されました',
            ['id' => $x->id] + $context->logContext()
        );
        return $x;
    }
}
