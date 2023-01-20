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
use Lib\Exceptions\InvalidArgumentException;
use Lib\Exceptions\NotFoundException;
use Lib\Logging;

/**
 * {@link \UseCase\UserBilling\UpdateUserBillingUseCase} の実装.
 */
final class UpdateUserBillingInteractor implements UpdateUserBillingUseCase
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

    /** {@inheritdoc} */
    public function handle(Context $context, int $id, array $values): UserBilling
    {
        $x = $this->transaction->run(function () use ($context, $id, $values): UserBilling {
            $userBilling = $this->lookupUserBilling($context, $id);
            $updateUserBilling = $userBilling->copy([
                'carriedOverAmount' => $values['carriedOverAmount'],
                'user' => $userBilling->user->copy([
                    'billingDestination' => $userBilling->user->billingDestination->copy([
                        'paymentMethod' => $values['paymentMethod'],
                    ]),
                    'bankAccount' => $values['bankAccount'],
                ]),
            ]);

            if ($updateUserBilling->totalAmount < 0) {
                throw new InvalidArgumentException('totalAmount of UserBilling must be 0 or greater');
            }

            return $this->repository->store($updateUserBilling->copy([
                'result' => $updateUserBilling->totalAmount === 0
                    ? UserBillingResult::none()
                    : UserBillingResult::pending(),
                'updatedAt' => Carbon::now(),
            ]));
        });
        $this->logger()->info(
            '利用者請求が更新されました',
            ['id' => $x->id] + $context->logContext()
        );
        return $x;
    }

    /**
     * 利用者請求を取得する.
     *
     * @param \Domain\Context\Context $context
     * @param int $id
     * @return \Domain\UserBilling\UserBilling
     */
    private function lookupUserBilling(Context $context, int $id): UserBilling
    {
        return $this->lookupUseCase->handle($context, Permission::updateUserBillings(), $id)
            ->headOption()
            ->getOrElse(function () use ($id): void {
                throw new NotFoundException("UserBilling({$id}) not found");
            });
    }
}
