<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Contract;

use Domain\Common\Carbon;
use Domain\Context\Context;
use Domain\Contract\Contract;
use Domain\Contract\ContractRepository;
use Domain\Permission\Permission;
use Domain\TransactionManager;
use Domain\TransactionManagerFactory;
use Lib\Exceptions\NotFoundException;
use Lib\Logging;

/**
 * 契約編集ユースケース実装.
 */
final class EditContractInteractor implements EditContractUseCase
{
    use Logging;

    private LookupContractUseCase $lookupUseCase;
    private ContractRepository $repository;
    private TransactionManager $transaction;

    /**
     * {@link \UseCase\Contract\EditContractInteractor} Constructor.
     *
     * @param \UseCase\Contract\LookupContractUseCase $lookupUseCase
     * @param \Domain\Contract\ContractRepository $repository
     * @param \Domain\TransactionManagerFactory $transactionManagerFactory
     */
    public function __construct(
        LookupContractUseCase $lookupUseCase,
        ContractRepository $repository,
        TransactionManagerFactory $transactionManagerFactory
    ) {
        $this->lookupUseCase = $lookupUseCase;
        $this->repository = $repository;
        $this->transaction = $transactionManagerFactory->factory($repository);
    }

    /** {@inheritdoc} */
    public function handle(Context $context, Permission $permission, int $userId, int $id, array $values): Contract
    {
        $entity = $this->lookupUseCase->handle($context, $permission, $userId, $id)
            ->headOption()
            ->getOrElse(function () use ($id): void {
                throw new NotFoundException("Contract({$id}) not found");
            });
        $x = $this->transaction->run(function () use ($entity, $values): Contract {
            $attrs = [
                'updatedAt' => Carbon::now(),
                'version' => $entity->version + 1,
            ];
            return $this->repository->store($entity->copy($attrs + $values));
        });
        $this->logger()->info(
            '契約が更新されました',
            ['id' => $x->id] + $context->logContext()
        );
        return $x;
    }
}
