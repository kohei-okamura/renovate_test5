<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\UserBilling;

use Domain\Context\Context;
use Domain\Job\Job as DomainJob;
use UseCase\Job\RunJobUseCase;

/**
 * {@link \UseCase\UserBilling\RunCreateWithdrawalTransactionJobUseCase} の実装.
 */
final class RunCreateWithdrawalTransactionJobInteractor implements RunCreateWithdrawalTransactionJobUseCase
{
    private RunJobUseCase $runJobUseCase;
    private CreateWithdrawalTransactionUseCase $createWithdrawalTransactionUseCase;

    /**
     * Constructor.
     *
     * @param \UseCase\Job\RunJobUseCase $runJobUseCase
     * @param \UseCase\UserBilling\CreateWithdrawalTransactionUseCase $createWithdrawalTransactionUseCase
     */
    public function __construct(
        RunJobUseCase $runJobUseCase,
        CreateWithdrawalTransactionUseCase $createWithdrawalTransactionUseCase
    ) {
        $this->runJobUseCase = $runJobUseCase;
        $this->createWithdrawalTransactionUseCase = $createWithdrawalTransactionUseCase;
    }

    /** {@inheritdoc} */
    public function handle(Context $context, DomainJob $domainJob, array $userBillingIds): void
    {
        $this->runJobUseCase->handle(
            $context,
            $domainJob,
            function () use ($context, $userBillingIds): void {
                $this->createWithdrawalTransactionUseCase->handle($context, $userBillingIds);
            }
        );
    }
}
