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
use Domain\ProvisionReport\DwsProvisionReport;
use Domain\ProvisionReport\DwsProvisionReportRepository;
use Domain\ProvisionReport\DwsProvisionReportStatus;
use Domain\TransactionManager;
use Domain\TransactionManagerFactory;
use Lib\Exceptions\NotFoundException;
use Lib\Logging;

/**
 * 障害福祉サービス：予実 状態更新ユースケース実装.
 */
final class UpdateDwsProvisionReportStatusInteractor implements UpdateDwsProvisionReportStatusUseCase
{
    use Logging;

    private GetDwsProvisionReportUseCase $getDwsProvisionReportUseCase;
    private DwsProvisionReportRepository $repository;
    private TransactionManager $transaction;

    /**
     * constructor.
     *
     * @param \UseCase\ProvisionReport\GetDwsProvisionReportUseCase $getDwsProvisionReportUseCase
     * @param \Domain\ProvisionReport\DwsProvisionReportRepository $repository
     * @param \Domain\TransactionManagerFactory $factory
     */
    public function __construct(
        GetDwsProvisionReportUseCase $getDwsProvisionReportUseCase,
        DwsProvisionReportRepository $repository,
        TransactionManagerFactory $factory
    ) {
        $this->getDwsProvisionReportUseCase = $getDwsProvisionReportUseCase;
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
    ): DwsProvisionReport {
        /** @var \Domain\ProvisionReport\DwsProvisionReport $dwsProvisionReport */
        $dwsProvisionReport = $this->getDwsProvisionReportUseCase->handle(
            $context,
            Permission::updateDwsProvisionReports(),
            $officeId,
            $userId,
            Carbon::parse($providedIn),
        )->getOrElse(function () use ($officeId, $userId, $providedIn) {
            throw new NotFoundException(
                "DwsProvisionReport(officeId: {$officeId}, userId: {$userId}, providedIn: {$providedIn}) not found."
            );
        });

        return $this->editDwsProvisionReport($context, $dwsProvisionReport, $values);
    }

    /**
     * 障害福祉サービス：予実を編集する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\ProvisionReport\DwsProvisionReport $provisionReport
     * @param array $values
     * @return \Domain\ProvisionReport\DwsProvisionReport
     */
    private function editDwsProvisionReport(Context $context, DwsProvisionReport $provisionReport, array $values): DwsProvisionReport
    {
        $x = $this->transaction->run(fn (): DwsProvisionReport => $this->repository->store(
            $provisionReport->copy(
                [
                    'fixedAt' => $values['status'] === DwsProvisionReportStatus::fixed()
                        ? Carbon::now()
                        : null,
                    'updatedAt' => Carbon::now(),
                ] + $values
            )
        ));
        $this->logger()->info(
            '障害福祉サービス：予実が更新されました',
            ['id' => $x->id] + $context->logContext()
        );
        return $x;
    }
}
