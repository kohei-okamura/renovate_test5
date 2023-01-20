<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Billing\LtcsBillingBundle;
use Domain\Billing\LtcsBillingStatement;
use Domain\Billing\LtcsBillingStatementRepository;
use Domain\Common\Decimal;
use Domain\Context\Context;
use Domain\Office\Office;
use Domain\TransactionManager;
use Domain\TransactionManagerFactory;
use Domain\User\User;
use ScalikePHP\Seq;

/**
 * 介護保険サービス：明細書生成ユースケース実装.
 */
final class CreateLtcsBillingStatementInteractor implements CreateLtcsBillingStatementUseCase
{
    private BuildLtcsBillingStatementUseCase $buildStatementUseCase;
    private LtcsBillingStatementRepository $repository;
    private TransactionManager $transaction;

    /**
     * {@link \UseCase\Billing\CreateLtcsBillingStatementInteractor} Constructor.
     *
     * @param BuildLtcsBillingStatementUseCase $buildStatementUseCase
     * @param LtcsBillingStatementRepository $repository
     * @param TransactionManagerFactory $factory
     */
    public function __construct(
        BuildLtcsBillingStatementUseCase $buildStatementUseCase,
        LtcsBillingStatementRepository $repository,
        TransactionManagerFactory $factory
    ) {
        $this->buildStatementUseCase = $buildStatementUseCase;
        $this->repository = $repository;
        $this->transaction = $factory->factory($repository);
    }

    /** {@inheritdoc} */
    public function handle(
        Context $context,
        LtcsBillingBundle $bundle,
        User $user,
        Office $office,
        Seq $details,
        Decimal $unitCost,
        Seq $reports
    ): LtcsBillingStatement {
        return $this->transaction->run(fn (): LtcsBillingStatement => $this->repository->store(
            $this->buildStatementUseCase->handle(
                $context,
                $bundle,
                $user,
                $office,
                $details,
                $unitCost,
                $reports
            )
        ));
    }
}
