<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Staff;

use Domain\Context\Context;
use Domain\Staff\StaffRememberTokenRepository;
use Domain\TransactionManager;
use Domain\TransactionManagerFactory;
use Lib\Logging;

/**
 * スタッフリメンバートークン削除ユースケース実装.
 */
final class RemoveStaffRememberTokenInteractor implements RemoveStaffRememberTokenUseCase
{
    use Logging;

    private StaffRememberTokenRepository $repository;
    private TransactionManager $transaction;

    /**
     * Constructor.
     *
     * @param \Domain\Staff\StaffRememberTokenRepository $repository
     * @param \Domain\TransactionManagerFactory $transactionManagerFactory
     */
    public function __construct(
        StaffRememberTokenRepository $repository,
        TransactionManagerFactory $transactionManagerFactory
    ) {
        $this->repository = $repository;
        $this->transaction = $transactionManagerFactory->factory($repository);
    }

    /** {@inheritdoc} */
    public function handle(Context $context, int $id): void
    {
        $this->transaction->run(function () use ($id): void {
            $this->repository->removeById($id);
        });
        $this->logger()->info('スタッフリメンバートークンが削除されました', ['id' => $id] + $context->logContext());
    }
}
