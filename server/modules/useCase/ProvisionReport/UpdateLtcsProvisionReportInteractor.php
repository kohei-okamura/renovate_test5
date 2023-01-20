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
use Domain\Contract\Contract;
use Domain\Office\Office;
use Domain\Permission\Permission;
use Domain\ProvisionReport\LtcsProvisionReport;
use Domain\ProvisionReport\LtcsProvisionReportRepository;
use Domain\ProvisionReport\LtcsProvisionReportStatus;
use Domain\TransactionManager;
use Domain\TransactionManagerFactory;
use Lib\Exceptions\NotFoundException;
use Lib\Logging;
use ScalikePHP\Option;
use UseCase\Contract\IdentifyContractUseCase;
use UseCase\Office\LookupOfficeUseCase;

/**
 * 介護保険サービス：予実更新ユースケース実装.
 */
class UpdateLtcsProvisionReportInteractor implements UpdateLtcsProvisionReportUseCase
{
    use Logging;

    private FindLtcsProvisionReportUseCase $findLtcsProvisionReportUseCase;
    private IdentifyContractUseCase $identifyContractUseCase;
    private LookupOfficeUseCase $lookupOfficeUseCase;
    private LtcsProvisionReportRepository $repository;
    private TransactionManager $transaction;

    /**
     * Constructor.
     *
     * @param \UseCase\ProvisionReport\FindLtcsProvisionReportUseCase $findLtcsProvisionReportUseCase
     * @param \UseCase\Contract\IdentifyContractUseCase $identifyContractUseCase
     * @param \UseCase\Office\LookupOfficeUseCase $lookupOfficeUseCase
     * @param \Domain\ProvisionReport\LtcsProvisionReportRepository $repository
     * @param \Domain\TransactionManagerFactory $factory
     */
    public function __construct(
        FindLtcsProvisionReportUseCase $findLtcsProvisionReportUseCase,
        IdentifyContractUseCase $identifyContractUseCase,
        LookupOfficeUseCase $lookupOfficeUseCase,
        LtcsProvisionReportRepository $repository,
        TransactionManagerFactory $factory
    ) {
        $this->findLtcsProvisionReportUseCase = $findLtcsProvisionReportUseCase;
        $this->identifyContractUseCase = $identifyContractUseCase;
        $this->lookupOfficeUseCase = $lookupOfficeUseCase;
        $this->repository = $repository;
        $this->transaction = $factory->factory($repository);
    }

    /** {@inheritdoc} */
    public function handle(Context $context, int $officeId, int $userId, string $providedIn, array $values): LtcsProvisionReport
    {
        $office = $this->getOffice($context, $officeId);
        $contract = $this->getContract($context, $officeId, $userId);

        return $this->findLtcsProvisionReportOption($context, $officeId, $userId, Carbon::parse($providedIn))
            ->map(fn (LtcsProvisionReport $ltcsProvisionReport): LtcsProvisionReport => $this->editLtcsProvisionReport(
                $context,
                $ltcsProvisionReport,
                $values
            ))
            ->getOrElse(fn (): LtcsProvisionReport => $this->createLtcsProvisionReport(
                $context,
                $office->id,
                $userId,
                $contract->id,
                Carbon::parse($providedIn),
                $values
            ));
    }

    /**
     * 事業所を取得する.
     *
     * @param \Domain\Context\Context $context
     * @param int $officeId
     * @return \Domain\Office\Office
     */
    private function getOffice(Context $context, int $officeId): Office
    {
        return $this->lookupOfficeUseCase
            ->handle($context, [Permission::updateLtcsProvisionReports()], $officeId)
            ->headOption()
            ->getOrElse(function () use ($officeId): void {
                throw new NotFoundException("Office({$officeId}) not found");
            });
    }

    /**
     * 契約を取得する.
     *
     * @param \Domain\Context\Context $context
     * @param int $officeId
     * @param int $userId
     * @return \Domain\Contract\Contract
     */
    private function getContract(Context $context, int $officeId, int $userId): Contract
    {
        return $this->identifyContractUseCase
            ->handle(
                $context,
                Permission::updateLtcsProvisionReports(),
                $officeId,
                $userId,
                ServiceSegment::longTermCare(),
                Carbon::now()
            )
            ->getOrElse(function (): void {
                throw new NotFoundException('Contract not found');
            });
    }

    /**
     * 介護保険サービス：予実を取得する.
     *
     * @param \Domain\Context\Context $context
     * @param int $officeId
     * @param int $userId
     * @param \Domain\Common\Carbon $providedIn
     * @return \Domain\ProvisionReport\LtcsProvisionReport[]|\ScalikePHP\Option
     */
    private function findLtcsProvisionReportOption(Context $context, int $officeId, int $userId, Carbon $providedIn): Option
    {
        return $this->findLtcsProvisionReportUseCase
            ->handle(
                $context,
                Permission::updateLtcsProvisionReports(),
                [
                    'officeId' => $officeId,
                    'userId' => $userId,
                    'providedIn' => $providedIn,
                ],
                ['all' => true],
            )
            ->list
            ->headOption();
    }

    /**
     * 介護保険サービス：予実を登録する.
     *
     * @param \Domain\Context\Context $context
     * @param int $officeId
     * @param int $userId
     * @param int $contractId
     * @param \Domain\Common\Carbon $providedIn
     * @param array $values
     * @throws \Throwable
     * @return \Domain\ProvisionReport\LtcsProvisionReport
     */
    private function createLtcsProvisionReport(Context $context, int $officeId, int $userId, int $contractId, Carbon $providedIn, array $values): LtcsProvisionReport
    {
        $x = $this->transaction->run(fn (): LtcsProvisionReport => $this->repository->store(
            LtcsProvisionReport::create(
                [
                    'officeId' => $officeId,
                    'userId' => $userId,
                    'contractId' => $contractId,
                    'providedIn' => Carbon::parse($providedIn),
                    'status' => LtcsProvisionReportStatus::inProgress(),
                    'createdAt' => Carbon::now(),
                    'updatedAt' => Carbon::now(),
                ] + $values
            )
        ));
        $this->logger()->info(
            '介護保険サービス：予実が登録されました',
            ['id' => $x->id] + $context->logContext()
        );
        return $x;
    }

    /**
     * 介護保険サービス：予実を編集する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\ProvisionReport\LtcsProvisionReport $ltcsProvisionReport
     * @param array $values
     * @throws \Throwable
     * @return \Domain\ProvisionReport\LtcsProvisionReport
     */
    private function editLtcsProvisionReport(Context $context, LtcsProvisionReport $ltcsProvisionReport, array $values): LtcsProvisionReport
    {
        $x = $this->transaction->run(fn (): LtcsProvisionReport => $this->repository->store(
            $ltcsProvisionReport->copy(['updatedAt' => Carbon::now()] + $values)
        ));
        $this->logger()->info(
            '介護保険サービス：予実が更新されました',
            ['id' => $x->id] + $context->logContext()
        );
        return $x;
    }
}
