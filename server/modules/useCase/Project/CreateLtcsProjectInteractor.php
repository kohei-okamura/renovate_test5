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
use Domain\Project\LtcsProject;
use Domain\Project\LtcsProjectRepository;
use Domain\TransactionManager;
use Domain\TransactionManagerFactory;
use Lib\Exceptions\NotFoundException;
use Lib\Logging;
use UseCase\Contract\IdentifyContractUseCase;
use UseCase\User\EnsureUserUseCase;

/**
 * 介護保険サービス：計画登録実装.
 */
class CreateLtcsProjectInteractor implements CreateLtcsProjectUseCase
{
    use Logging;

    private EnsureUserUseCase $ensureUserUseCase;
    private IdentifyContractUseCase $identifyContractUseCase;
    private LtcsProjectRepository $repository;
    private TransactionManager $transaction;

    /**
     * Constructor.
     *
     * @param \UseCase\User\EnsureUserUseCase $ensureUserUseCase
     * @param \UseCase\Contract\IdentifyContractUseCase $identifyContractUseCase
     * @param \Domain\Project\LtcsProjectRepository $repository
     * @param \Domain\TransactionManagerFactory $transactionManagerFactory
     */
    public function __construct(
        EnsureUserUseCase $ensureUserUseCase,
        IdentifyContractUseCase $identifyContractUseCase,
        LtcsProjectRepository $repository,
        TransactionManagerFactory $transactionManagerFactory
    ) {
        $this->ensureUserUseCase = $ensureUserUseCase;
        $this->identifyContractUseCase = $identifyContractUseCase;
        $this->repository = $repository;
        $this->transaction = $transactionManagerFactory->factory($repository);
    }

    /** {@inheritdoc} */
    public function handle(Context $context, int $userId, LtcsProject $ltcsProject): LtcsProject
    {
        $x = $this->transaction->run(function () use ($context, $userId, $ltcsProject): LtcsProject {
            $officeId = $ltcsProject->officeId;
            $this->ensureUserUseCase->handle($context, Permission::createLtcsProjects(), $userId);
            $contractId = $this->identifyContractUseCase
                ->handle(
                    $context,
                    Permission::createLtcsProjects(),
                    $officeId,
                    $userId,
                    ServiceSegment::longTermCare(),
                    Carbon::now()
                )
                ->map(fn (Contract $x): int => $x->id)
                ->getOrElse(function () use ($officeId): void {
                    throw new NotFoundException("Contract with Office({$officeId}) not found");
                });
            return $this->repository->store($ltcsProject->copy([
                'organizationId' => $context->organization->id,
                'contractId' => $contractId,
                'userId' => $userId,
                'version' => 1,
                'createdAt' => Carbon::now(),
                'updatedAt' => Carbon::now(),
            ]));
        });
        $this->logger()->info(
            '介護保険サービス：計画が登録されました',
            ['id' => $x->id] + $context->logContext()
        );
        return $x;
    }
}
