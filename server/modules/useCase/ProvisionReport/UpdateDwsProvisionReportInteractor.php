<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\ProvisionReport;

use Domain\Common\Carbon;
use Domain\Common\ServiceSegment;
use Domain\Context\Context;
use Domain\Permission\Permission;
use Domain\ProvisionReport\DwsProvisionReport;
use Domain\ProvisionReport\DwsProvisionReportRepository;
use Domain\ProvisionReport\DwsProvisionReportStatus;
use Domain\TransactionManager;
use Domain\TransactionManagerFactory;
use Lib\Exceptions\NotFoundException;
use Lib\Logging;
use UseCase\Contract\IdentifyContractUseCase;

/**
 * 障害福祉サービス：予実更新ユースケース実装.
 */
final class UpdateDwsProvisionReportInteractor implements UpdateDwsProvisionReportUseCase
{
    use Logging;

    private FindDwsProvisionReportUseCase $findDwsProvisionReportUseCase;
    private IdentifyContractUseCase $identifyContractUseCase;
    private DwsProvisionReportRepository $repository;
    private TransactionManager $transaction;

    /**
     * constructor.
     *
     * @param \UseCase\ProvisionReport\FindDwsProvisionReportUseCase $findDwsProvisionReportUseCase
     * @param \UseCase\Contract\IdentifyContractUseCase $identifyContractUseCase
     * @param \Domain\ProvisionReport\DwsProvisionReportRepository $repository
     * @param \Domain\TransactionManagerFactory $factory
     */
    public function __construct(
        FindDwsProvisionReportUseCase $findDwsProvisionReportUseCase,
        IdentifyContractUseCase $identifyContractUseCase,
        DwsProvisionReportRepository $repository,
        TransactionManagerFactory $factory
    ) {
        $this->findDwsProvisionReportUseCase = $findDwsProvisionReportUseCase;
        $this->identifyContractUseCase = $identifyContractUseCase;
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
        $parsedProvidedIn = Carbon::parse($providedIn);
        /** @var \Domain\Contract\Contract $contract */
        $contract = $this->identifyContractUseCase
            ->handle(
                $context,
                Permission::updateDwsProvisionReports(),
                $officeId,
                $userId,
                ServiceSegment::disabilitiesWelfare(),
                $parsedProvidedIn->lastOfMonth()
            )
            ->getOrElse(function () {
                throw new NotFoundException('Contract not found');
            });

        return $this->findDwsProvisionReportUseCase
            ->handle(
                $context,
                Permission::updateDwsProvisionReports(),
                [
                    'officeId' => $officeId,
                    'userId' => $userId,
                    'providedIn' => $parsedProvidedIn,
                ],
                ['all' => true],
            )
            ->list
            ->headOption()
            ->map(function (DwsProvisionReport $dwsProvisionReport) use ($context, $values): DwsProvisionReport {
                return $this->editDwsProvisionReport($context, $dwsProvisionReport, $values);
            })
            ->getOrElse(function () use ($context, $officeId, $userId, $parsedProvidedIn, $values, $contract): DwsProvisionReport {
                return $this->createDwsProvisionReport($context, $officeId, $userId, $contract->id, $parsedProvidedIn, $values);
            });
    }

    /**
     * 障害福祉サービス：予実を登録する.
     *
     * @param \Domain\Context\Context $context
     * @param int $officeId
     * @param int $userId
     * @param int $contractId
     * @param \Domain\Common\Carbon $providedIn
     * @param array $values
     * @throws \Throwable
     * @return \Domain\ProvisionReport\DwsProvisionReport
     */
    private function createDwsProvisionReport(Context $context, int $officeId, int $userId, int $contractId, Carbon $providedIn, array $values): DwsProvisionReport
    {
        $x = $this->transaction->run(fn (): DwsProvisionReport => $this->repository->store(
            DwsProvisionReport::create(
                [
                    'officeId' => $officeId,
                    'userId' => $userId,
                    'contractId' => $contractId,
                    'providedIn' => Carbon::parse($providedIn),
                    'status' => DwsProvisionReportStatus::inProgress(),
                    'createdAt' => Carbon::now(),
                    'updatedAt' => Carbon::now(),
                ] + $values
            )
        ));
        $this->logger()->info(
            '障害福祉サービス：予実が登録されました',
            ['id' => $x->id] + $context->logContext()
        );
        return $x;
    }

    /**
     * 障害福祉サービス：予実を編集する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\ProvisionReport\DwsProvisionReport $ltcsProvisionReport
     * @param array $values
     * @throws \Throwable
     * @return \Domain\ProvisionReport\DwsProvisionReport
     */
    private function editDwsProvisionReport(Context $context, DwsProvisionReport $ltcsProvisionReport, array $values): DwsProvisionReport
    {
        $x = $this->transaction->run(fn (): DwsProvisionReport => $this->repository->store(
            $ltcsProvisionReport->copy(['updatedAt' => Carbon::now()] + $values)
        ));
        $this->logger()->info(
            '障害福祉サービス：予実が更新されました',
            ['id' => $x->id] + $context->logContext()
        );
        return $x;
    }
}
