<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\UserBilling;

use Domain\Common\Carbon;
use Domain\Context\Context;
use Domain\Permission\Permission;
use Domain\TransactionManager;
use Domain\TransactionManagerFactory;
use Domain\UserBilling\UserBilling;
use Domain\UserBilling\UserBillingRepository;
use Domain\UserBilling\UserBillingResult;
use Lib\Exceptions\NotFoundException;
use Lib\Logging;

/**
 * {@link \UseCase\UserBilling\DeleteUserBillingDepositUseCase} の実装
 */
class DeleteUserBillingDepositInteractor implements DeleteUserBillingDepositUseCase
{
    use Logging;

    private LookupUserBillingUseCase $lookupUserBillingUseCase;
    private UserBillingRepository $repository;
    private TransactionManager $transaction;

    /**
     * Constructor.
     *
     * @param \UseCase\UserBilling\LookupUserBillingUseCase $lookupUserBillingUseCase
     * @param \Domain\UserBilling\UserBillingRepository $repository
     * @param \Domain\TransactionManagerFactory $transactionManagerFactory
     */
    public function __construct(
        LookupUserBillingUseCase $lookupUserBillingUseCase,
        UserBillingRepository $repository,
        TransactionManagerFactory $transactionManagerFactory
    ) {
        $this->lookupUserBillingUseCase = $lookupUserBillingUseCase;
        $this->repository = $repository;
        $this->transaction = $transactionManagerFactory->factory($repository);
    }

    /** {@inheritdoc} */
    public function handle(Context $context, int ...$ids): void
    {
        $this->transaction->run(function () use ($context, $ids): void {
            $useBillings = $this->lookupUserBillingUseCase->handle($context, Permission::updateUserBillings(), ...$ids);
            if ($useBillings->isEmpty()) {
                throw new NotFoundException('UserBilling not found');
            }
            $useBillings->each(function (UserBilling $x): void {
                $this->repository->store($x->copy([
                    'result' => UserBillingResult::pending(),
                    'depositedAt' => null,
                    'updatedAt' => Carbon::now(),
                ]));
            });
        });
        $this->logger()->info(
            '利用者請求入金日が削除されました',
            // TODO DEV-1577 IDの複数出力方法は検討中なので暫定的に空文字
            ['id' => ''] + $context->logContext()
        );
    }
}
