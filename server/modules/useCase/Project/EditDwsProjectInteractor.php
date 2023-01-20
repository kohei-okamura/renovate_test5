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

/**
 * 障害福祉サービス：計画編集実装.
 */
final class EditDwsProjectInteractor implements EditDwsProjectUseCase
{
    use Logging;

    private LookupDwsProjectUseCase $LookupDwsProjectUseCase;
    private IdentifyContractUseCase $identifyContractUseCase;
    private DwsProjectRepository $repository;
    private TransactionManager $transaction;

    /**
     * Constructor.
     *
     * @param \Domain\Project\DwsProjectRepository $repository
     * @param \UseCase\Contract\IdentifyContractUseCase $identifyContractUseCase
     * @param \UseCase\Project\LookupDwsProjectUseCase $LookupDwsProjectUseCase
     * @param \Domain\TransactionManagerFactory $transactionManagerFactory
     */
    public function __construct(
        DwsProjectRepository $repository,
        IdentifyContractUseCase $identifyContractUseCase,
        LookupDwsProjectUseCase $LookupDwsProjectUseCase,
        TransactionManagerFactory $transactionManagerFactory
    ) {
        $this->LookupDwsProjectUseCase = $LookupDwsProjectUseCase;
        $this->identifyContractUseCase = $identifyContractUseCase;
        $this->repository = $repository;
        $this->transaction = $transactionManagerFactory->factory($repository);
    }

    /** {@inheritdoc} */
    public function handle(Context $context, int $userId, int $id, array $values): DwsProject
    {
        $x = $this->transaction->run(function () use ($context, $userId, $id, $values): DwsProject {
            $officeId = $values['officeId'];
            $entity = $this->LookupDwsProjectUseCase->handle($context, Permission::updateDwsProjects(), $userId, $id)
                ->headOption()
                ->getOrElse(function () use ($id): void {
                    throw new NotFoundException("DwsProject({$id}) not found");
                });
            $contractId = $this->identifyContractUseCase
                ->handle(
                    $context,
                    Permission::updateDwsProjects(),
                    $officeId,
                    $userId,
                    ServiceSegment::disabilitiesWelfare(),
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
            '障害福祉サービス：計画が更新されました',
            ['id' => $x->id] + $context->logContext()
        );
        return $x;
    }
}
