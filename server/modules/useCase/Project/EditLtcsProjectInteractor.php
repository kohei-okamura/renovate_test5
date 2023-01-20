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

/**
 * 介護保険サービス：計画編集実装.
 */
final class EditLtcsProjectInteractor implements EditLtcsProjectUseCase
{
    use Logging;

    private LookupLtcsProjectUseCase $LookupLtcsProjectUseCase;
    private IdentifyContractUseCase $identifyContractUseCase;
    private LtcsProjectRepository $repository;
    private TransactionManager $transaction;

    /**
     * Constructor.
     *
     * @param \Domain\Project\LtcsProjectRepository $repository
     * @param \UseCase\Contract\IdentifyContractUseCase $identifyContractUseCase
     * @param \UseCase\Project\LookupLtcsProjectUseCase $LookupLtcsProjectUseCase
     * @param \Domain\TransactionManagerFactory $transactionManagerFactory
     */
    public function __construct(
        LtcsProjectRepository $repository,
        IdentifyContractUseCase $identifyContractUseCase,
        LookupLtcsProjectUseCase $LookupLtcsProjectUseCase,
        TransactionManagerFactory $transactionManagerFactory
    ) {
        $this->LookupLtcsProjectUseCase = $LookupLtcsProjectUseCase;
        $this->identifyContractUseCase = $identifyContractUseCase;
        $this->repository = $repository;
        $this->transaction = $transactionManagerFactory->factory($repository);
    }

    /** {@inheritdoc} */
    public function handle(Context $context, int $userId, int $id, array $values): LtcsProject
    {
        $x = $this->transaction->run(function () use ($context, $userId, $id, $values): LtcsProject {
            $officeId = $values['officeId'];
            $entity = $this->LookupLtcsProjectUseCase->handle($context, Permission::updateLtcsProjects(), $userId, $id)
                ->headOption()
                ->getOrElse(function () use ($id): void {
                    throw new NotFoundException("LtcsProject({$id}) not found");
                });
            $contractId = $this->identifyContractUseCase
                ->handle(
                    $context,
                    Permission::updateLtcsProjects(),
                    $officeId,
                    $userId,
                    ServiceSegment::longTermCare(),
                    Carbon::now()
                )
                ->map(fn (Contract $x): int => $x->id)
                ->getOrElse(function () use ($officeId): void {
                    throw new NotFoundException("Contract with Office({$officeId}) not found");
                });
            return $this->repository->store($entity->copy($values + [
                'contractId' => $contractId,
                'userId' => $userId,
                'version' => $entity->version + 1,
                'updatedAt' => Carbon::now(),
            ]));
        });
        $this->logger()->info(
            '介護保険サービス：計画が更新されました',
            ['id' => $x->id] + $context->logContext()
        );
        return $x;
    }
}
