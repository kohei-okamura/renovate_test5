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
 * {@link \UseCase\UserBilling\RunDeleteUserBillingDepositJobUseCase} の実装.
 */
final class RunDeleteUserBillingDepositJobInteractor implements RunDeleteUserBillingDepositJobUseCase
{
    private RunJobUseCase $runJobUseCase;
    private DeleteUserBillingDepositUseCase $deleteDepositUseCase;

    /**
     * Constructor.
     *
     * @param \UseCase\Job\RunJobUseCase $runJobUseCase
     * @param \UseCase\Shift\CancelShiftUseCase $cancelShiftUseCase
     * @param DeleteUserBillingDepositUseCase $deleteDepositUseCase
     */
    public function __construct(
        RunJobUseCase $runJobUseCase,
        DeleteUserBillingDepositUseCase $deleteDepositUseCase
    ) {
        $this->runJobUseCase = $runJobUseCase;
        $this->deleteDepositUseCase = $deleteDepositUseCase;
    }

    /** {@inheritdoc} */
    public function handle(Context $context, DomainJob $domainJob, int ...$ids): void
    {
        $this->runJobUseCase->handle(
            $context,
            $domainJob,
            function () use ($context, $ids): void {
                $this->deleteDepositUseCase->handle($context, ...$ids);
            }
        );
    }
}
