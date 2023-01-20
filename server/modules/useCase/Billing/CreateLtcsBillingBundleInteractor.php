<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Billing\LtcsBilling;
use Domain\Billing\LtcsBillingBundle;
use Domain\Billing\LtcsBillingBundleRepository;
use Domain\Common\Carbon;
use Domain\Context\Context;
use Domain\Permission\Permission;
use Domain\ProvisionReport\LtcsProvisionReport;
use Domain\TransactionManager;
use Domain\TransactionManagerFactory;
use Lib\Exceptions\NotFoundException;
use ScalikePHP\Seq;
use UseCase\User\LookupUserUseCase;

/**
 * 介護保険サービス：請求単位生成ユースケース実装.
 */
final class CreateLtcsBillingBundleInteractor implements CreateLtcsBillingBundleUseCase
{
    private LtcsBillingBundleRepository $repository;
    private TransactionManager $transaction;

    /**
     * {@link \UseCase\Billing\CreateLtcsBillingBundleInteractor} constructor.
     *
     * @param \UseCase\Billing\BuildLtcsServiceDetailListUseCase $buildServiceDetailListUseCase
     * @param \UseCase\User\LookupUserUseCase $lookupUserUseCase
     * @param \Domain\Billing\LtcsBillingBundleRepository $repository
     * @param \Domain\TransactionManagerFactory $transactionManagerFactory
     */
    public function __construct(
        private BuildLtcsServiceDetailListUseCase $buildServiceDetailListUseCase,
        private LookupUserUseCase $lookupUserUseCase,
        LtcsBillingBundleRepository $repository,
        TransactionManagerFactory $transactionManagerFactory
    ) {
        $this->repository = $repository;
        $this->transaction = $transactionManagerFactory->factory($repository);
    }

    /** {@inheritdoc} */
    public function handle(Context $context, LtcsBilling $billing, Carbon $providedIn, Seq $reports): LtcsBillingBundle
    {
        return $this->transaction->run(function () use ($context, $billing, $providedIn, $reports): LtcsBillingBundle {
            $userIds = $reports->map(fn (LtcsProvisionReport $x): int => $x->userId);
            $users = $this->lookupUsers($context, $userIds);
            $details = $this->buildServiceDetailListUseCase->handle($context, $providedIn, $reports, $users);
            $bundle = LtcsBillingBundle::create([
                'billingId' => $billing->id,
                'providedIn' => $providedIn,
                'details' => $details,
                'createdAt' => Carbon::now(),
                'updatedAt' => Carbon::now(),
            ]);
            return $this->repository->store($bundle);
        });
    }

    /**
     * 利用者を取得する.
     *
     * @param \Domain\Context\Context $context
     * @param int[]&\ScalikePHP\Seq $userIds
     * @return \Domain\User\User[]&\ScalikePHP\Seq
     */
    private function lookupUsers(Context $context, Seq $userIds): Seq
    {
        $users = $this->lookupUserUseCase
            ->handle($context, Permission::createBillings(), ...$userIds->toArray());
        if ($users->isEmpty()) {
            $x = implode(',', $userIds);
            throw new NotFoundException("User ({$x}) not found");
        }
        return $users;
    }
}
