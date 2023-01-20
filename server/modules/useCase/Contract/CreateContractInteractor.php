<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Contract;

use Domain\Common\ServiceSegment;
use Domain\Context\Context;
use Domain\Contract\Contract;
use Domain\Contract\ContractRepository;
use Domain\Permission\Permission;
use Domain\TransactionManager;
use Domain\TransactionManagerFactory;
use Lib\Exceptions\InvalidArgumentException;
use Lib\Logging;
use UseCase\User\EnsureUserUseCase;

/**
 * 契約登録実装.
 */
final class CreateContractInteractor implements CreateContractUseCase
{
    use Logging;

    private EnsureUserUseCase $ensureUserUseCase;
    private ContractRepository $repository;
    private TransactionManager $transaction;

    /**
     * Constructor.
     *
     * @param \UseCase\User\EnsureUserUseCase $ensureUserUseCase
     * @param \Domain\Contract\ContractRepository $repository
     * @param \Domain\TransactionManagerFactory $transactionManagerFactory
     */
    public function __construct(
        EnsureUserUseCase $ensureUserUseCase,
        ContractRepository $repository,
        TransactionManagerFactory $transactionManagerFactory
    ) {
        $this->ensureUserUseCase = $ensureUserUseCase;
        $this->repository = $repository;
        $this->transaction = $transactionManagerFactory->factory($repository);
    }

    /** {@inheritdoc} */
    public function handle(Context $context, int $userId, Contract $contract): Contract
    {
        $permission = $this->permissionFromContract($contract);
        $this->ensureUserUseCase->handle($context, $permission, $userId);
        $x = $this->transaction->run(fn (): Contract => $this->repository->store($contract->copy([
            'organizationId' => $context->organization->id,
            'userId' => $userId,
        ])));
        $this->logger()->info(
            '契約が登録されました',
            ['id' => $x->id] + $context->logContext()
        );
        return $x;
    }

    /**
     * 契約のインスタンスから適切なPermissionを特定する.
     *
     * @param \Domain\Contract\Contract $contract
     * @return \Domain\Permission\Permission
     */
    private function permissionFromContract(Contract $contract): Permission
    {
        switch ($contract->serviceSegment) {
            case ServiceSegment::disabilitiesWelfare():
                return Permission::createDwsContracts();
            case ServiceSegment::longTermCare():
                return Permission::createLtcsContracts();
            default:
                throw new InvalidArgumentException('ServiceSegment[' . $contract->serviceSegment->value() . '] not supported'); // @codeCoverageIgnore
        }
    }
}
