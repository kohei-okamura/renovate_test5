<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Context\Context;
use Domain\Job\Job as DomainJob;
use UseCase\Job\RunJobUseCase;

/**
 * 介護保険サービス：明細書リフレッシュジョブ実行ユースケース実装.
 */
final class RunRefreshLtcsBillingStatementJobInteractor implements RunRefreshLtcsBillingStatementJobUseCase
{
    private RunJobUseCase $runJobUseCase;
    private RefreshLtcsBillingStatementUseCase $useCase;

    /**
     * Constructor.
     *
     * @param \UseCase\Job\RunJobUseCase $runJobUseCase
     * @param \UseCase\Billing\RefreshLtcsBillingStatementUseCase $useCase
     */
    public function __construct(
        RunJobUseCase $runJobUseCase,
        RefreshLtcsBillingStatementUseCase $useCase
    ) {
        $this->runJobUseCase = $runJobUseCase;
        $this->useCase = $useCase;
    }

    /** {@inheritdoc} */
    public function handle(Context $context, DomainJob $domainJob, int $billingId, array $ids): void
    {
        $this->runJobUseCase->handle(
            $context,
            $domainJob,
            function () use ($context, $billingId, $ids): void {
                $this->useCase->handle($context, $billingId, $ids);
            }
        );
    }
}
