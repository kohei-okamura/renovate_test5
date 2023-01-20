<?php

declare(strict_types=1);
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */

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
 * 利用者請求の入金日更新ユースケース実装.
 */
class UpdateUserBillingDepositInteractor implements UpdateUserBillingDepositUseCase
{
    use Logging;

    private LookupUserBillingUseCase $lookupUseCase;
    private UserBillingRepository $repository;
    private TransactionManager $transaction;

    /**
     * Constructor.
     *
     * @param \UseCase\UserBilling\LookupUserBillingUseCase $lookupUseCase
     * @param \Domain\UserBilling\UserBillingRepository $repository
     * @param \Domain\TransactionManagerFactory $transactionManagerFactory
     */
    public function __construct(
        LookupUserBillingUseCase $lookupUseCase,
        UserBillingRepository $repository,
        TransactionManagerFactory $transactionManagerFactory
    ) {
        $this->lookupUseCase = $lookupUseCase;
        $this->repository = $repository;
        $this->transaction = $transactionManagerFactory->factory($repository);
    }

    /** {@inheritdoc}*/
    public function handle(Context $context, Carbon $depositedAt, array $ids): void
    {
        $this->transaction->run(function () use ($context, $depositedAt, $ids) {
            $entities = $this->lookupUseCase->handle($context, Permission::updateUserBillings(), ...$ids);
            if (count($entities) !== count($ids)) {
                throw new NotFoundException('UserBilling not found');
            }

            $entities->each(fn ($x): UserBilling => $this->repository->store($x->copy([
                'result' => UserBillingResult::paid(),
                'depositedAt' => $depositedAt,
                'updatedAt' => Carbon::now(),
            ])));
        });
        $this->logger()->info(
            '利用者請求の入金日が更新されました',
            // TODO: IDの複数出力方法はDEV-1577
            ['id' => ''] + $context->logContext()
        );
    }
}
