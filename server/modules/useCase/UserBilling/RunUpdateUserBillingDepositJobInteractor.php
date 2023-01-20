<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\UserBilling;

use Domain\Common\Carbon;
use Domain\Context\Context;
use Domain\Job\Job as DomainJob;
use UseCase\Job\RunJobUseCase;

/**
 * 利用者請求入金日更新ジョブ実行ユースケース実装.
 */
final class RunUpdateUserBillingDepositJobInteractor implements RunUpdateUserBillingDepositJobUseCase
{
    private RunJobUseCase $runJobUseCase;
    private UpdateUserBillingDepositUseCase $useCase;

    /**
     * Constructor.
     *
     * @param \UseCase\Job\RunJobUseCase $runJobUseCase
     * @param \UseCase\UserBilling\UpdateUserBillingDepositUseCase $useCase
     */
    public function __construct(RunJobUseCase $runJobUseCase, UpdateUserBillingDepositUseCase $useCase)
    {
        $this->runJobUseCase = $runJobUseCase;
        $this->useCase = $useCase;
    }

    /** {@inheritdoc} */
    public function handle(Context $context, DomainJob $domainJob, Carbon $depositedAt, array $ids): void
    {
        $this->runJobUseCase->handle(
            $context,
            $domainJob,
            function () use ($context, $depositedAt, $ids): void {
                $this->useCase->handle($context, $depositedAt, $ids);
            }
        );
    }
}
