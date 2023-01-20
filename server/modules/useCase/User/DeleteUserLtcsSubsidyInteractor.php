<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\User;

use Domain\Context\Context;
use Domain\Permission\Permission;
use Domain\TransactionManager;
use Domain\TransactionManagerFactory;
use Domain\User\UserLtcsSubsidyRepository;
use Lib\Exceptions\NotFoundException;
use Lib\Logging;

/**
 * 公費情報削除ユースケース実装.
 */
class DeleteUserLtcsSubsidyInteractor implements DeleteUserLtcsSubsidyUseCase
{
    use Logging;

    private LookupUserLtcsSubsidyUseCase $lookupUseCase;
    private UserLtcsSubsidyRepository $repository;
    private TransactionManager $transaction;

    /**
     * constructor.
     *
     * @param \UseCase\User\LookupUserLtcsSubsidyUseCase $lookupUseCase
     * @param \Domain\User\UserLtcsSubsidyRepository $userLtcsSubsidyRepository
     * @param \Domain\TransactionManagerFactory $transactionManagerFactory
     */
    public function __construct(
        LookupUserLtcsSubsidyUseCase $lookupUseCase,
        UserLtcsSubsidyRepository $userLtcsSubsidyRepository,
        TransactionManagerFactory $transactionManagerFactory
    ) {
        $this->lookupUseCase = $lookupUseCase;
        $this->repository = $userLtcsSubsidyRepository;
        $this->transaction = $transactionManagerFactory->factory($userLtcsSubsidyRepository);
    }

    /** {@inheritdoc} */
    public function handle(Context $context, int $userId, int $id): void
    {
        $this->transaction->run(function () use ($context, $userId, $id): void {
            $this->lookupUseCase
                ->handle($context, Permission::deleteUserLtcsSubsidies(), $userId, $id)
                ->headOption()
                ->getOrElse(function () use ($id): void {
                    throw new NotFoundException("UserLtcsSubsidy({$id}) not found.");
                });
            $this->repository->removeById($id);
        });
        $this->logger()->info(
            '公費情報が削除されました',
            ['id' => $id] + $context->logContext()
        );
    }
}
