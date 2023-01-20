<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Project;

use Domain\Common\Carbon;
use Domain\Common\ServiceSegment;
use Domain\Context\Context;
use Domain\Contract\Contract;
use Domain\Permission\Permission;
use Domain\Project\DwsProject;
use Domain\Project\DwsProjectRepository;
use Domain\TransactionManager;
use Domain\TransactionManagerFactory;
use Lib\Exceptions\NotFoundException;
use Lib\Logging;
use UseCase\Contract\IdentifyContractUseCase;
use UseCase\User\EnsureUserUseCase;

/**
 * 障害福祉サービス：計画登録実装.
 */
class CreateDwsProjectInteractor implements CreateDwsProjectUseCase
{
    use Logging;

    private EnsureUserUseCase $ensureUserUseCase;
    private IdentifyContractUseCase $identifyContractUseCase;
    private DwsProjectRepository $repository;
    private TransactionManager $transaction;

    /**
     * Constructor.
     *
     * @param \UseCase\User\EnsureUserUseCase $ensureUserUseCase
     * @param \UseCase\Contract\IdentifyContractUseCase $identifyContractUseCase
     * @param \Domain\Project\DwsProjectRepository $repository
     * @param TransactionManagerFactory $transactionManagerFactory
     */
    public function __construct(
        EnsureUserUseCase $ensureUserUseCase,
        IdentifyContractUseCase $identifyContractUseCase,
        DwsProjectRepository $repository,
        TransactionManagerFactory $transactionManagerFactory
    ) {
        $this->ensureUserUseCase = $ensureUserUseCase;
        $this->identifyContractUseCase = $identifyContractUseCase;
        $this->repository = $repository;
        $this->transaction = $transactionManagerFactory->factory($repository);
    }

    /** {@inheritdoc} */
    public function handle(Context $context, int $userId, DwsProject $dwsProject): DwsProject
    {
        $x = $this->transaction->run(function () use ($context, $userId, $dwsProject): DwsProject {
            $this->ensureUserUseCase->handle($context, Permission::createDwsProjects(), $userId);
            $officeId = $dwsProject->officeId;
            $contractId = $this->identifyContractUseCase
                ->handle(
                    $context,
                    Permission::createDwsProjects(),
                    $officeId,
                    $userId,
                    ServiceSegment::disabilitiesWelfare(),
                    Carbon::now()
                )
                ->map(fn (Contract $x): int => $x->id)
                ->getOrElse(function () use ($officeId): void {
                    throw new NotFoundException("Contract with Office({$officeId}) not found");
                });
            return $this->repository->store($dwsProject->copy([
                'organizationId' => $context->organization->id,
                'contractId' => $contractId,
                'userId' => $userId,
                'version' => 1,
                'createdAt' => Carbon::now(),
                'updatedAt' => Carbon::now(),
            ]));
        });
        $this->logger()->info(
            '障害福祉サービス：計画が登録されました',
            ['id' => $x->id] + $context->logContext()
        );
        return $x;
    }
}
