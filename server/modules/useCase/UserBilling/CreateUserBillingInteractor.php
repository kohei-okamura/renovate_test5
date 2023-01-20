<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\UserBilling;

use Domain\Common\Carbon;
use Domain\Context\Context;
use Domain\Office\Office;
use Domain\TransactionManager;
use Domain\TransactionManagerFactory;
use Domain\User\User;
use Domain\UserBilling\UserBilling;
use Domain\UserBilling\UserBillingRepository;
use Lib\Exceptions\LogicException;
use ScalikePHP\Option;

/**
 * 利用者請求生成ユースケース実装.
 */
final class CreateUserBillingInteractor implements CreateUserBillingUseCase
{
    private UserBillingRepository $repository;
    private TransactionManager $transaction;
    private BuildUserBillingUseCase $buildUseCase;

    /**
     * {@link \UseCase\UserBilling\CreateUserBillingInteractor} Constructor.
     *
     * @param \Domain\UserBilling\UserBillingRepository $repository
     * @param \Domain\TransactionManagerFactory $factory
     * @param \UseCase\UserBilling\BuildUserBillingUseCase $buildUseCase
     */
    public function __construct(
        UserBillingRepository $repository,
        TransactionManagerFactory $factory,
        BuildUserBillingUseCase $buildUseCase
    ) {
        $this->repository = $repository;
        $this->transaction = $factory->factory($repository);
        $this->buildUseCase = $buildUseCase;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(
        Context $context,
        User $user,
        Office $office,
        Carbon $providedIn,
        Option $dwsBillingStatement,
        Option $ltcsBillingStatement,
        Option $dwsProvisionReport,
        Option $ltcsProvisionReport
    ): UserBilling {
        // 障害・介保の明細書と予実すべてが存在しない場合は利用者請求を組み立てられないため例外
        $unableToCreate = $dwsBillingStatement->isEmpty()
            && $ltcsBillingStatement->isEmpty()
            && $dwsProvisionReport->isEmpty()
            && $ltcsProvisionReport->isEmpty();
        if ($unableToCreate) {
            throw new LogicException("Cannot build UserBilling User（{$user->id}） Office（{$office->id}）");
        }

        $userBilling = $this->buildUseCase->handle(
            $context,
            $user,
            $office,
            $providedIn,
            $dwsBillingStatement,
            $ltcsBillingStatement,
            $dwsProvisionReport,
            $ltcsProvisionReport
        );
        return $this->transaction->run(fn (): UserBilling => $this->repository->store($userBilling));
    }
}
