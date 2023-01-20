<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\LtcsInsCard;

use Domain\Context\Context;
use Domain\LtcsInsCard\LtcsInsCardRepository;
use Domain\Permission\Permission;
use Domain\TransactionManager;
use Domain\TransactionManagerFactory;
use Lib\Exceptions\NotFoundException;
use Lib\Logging;

/**
 * 介護保険被保険者証編集実装.
 */
final class DeleteLtcsInsCardInteractor implements DeleteLtcsInsCardUseCase
{
    use Logging;

    private LookupLtcsInsCardUseCase $lookupUseCase;
    private LtcsInsCardRepository $repository;
    private TransactionManager $transaction;

    /**
     * Constructor.
     *
     * @param \UseCase\LtcsInsCard\LookupLtcsInsCardUseCase $lookupUseCase
     * @param \Domain\LtcsInsCard\LtcsInsCardRepository $repository
     * @param \Domain\TransactionManagerFactory $transactionManagerFactory
     */
    public function __construct(
        LookupLtcsInsCardUseCase $lookupUseCase,
        LtcsInsCardRepository $repository,
        TransactionManagerFactory $transactionManagerFactory
    ) {
        $this->lookupUseCase = $lookupUseCase;
        $this->repository = $repository;
        $this->transaction = $transactionManagerFactory->factory($repository);
    }

    /** {@inheritdoc} */
    public function handle(Context $context, int $userId, int $id): void
    {
        $this->lookupUseCase
            ->handle($context, Permission::deleteLtcsInsCards(), $userId, $id)
            ->headOption()
            ->getOrElse(function () use ($id): void {
                throw new NotFoundException("LtcsInsCard({$id}) not found");
            });

        $this->transaction->run(function () use ($id): void {
            $this->repository->removeById($id);
        });

        $this->logger()->info(
            '介護保険被保険者証が削除されました',
            ['id' => $id] + $context->logContext()
        );
    }
}
