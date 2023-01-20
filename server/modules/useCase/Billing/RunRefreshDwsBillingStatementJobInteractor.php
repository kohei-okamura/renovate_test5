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
 * 障害福祉サービス：明細書等リフレッシュジョブ実行ユースケース実装.
 */
final class RunRefreshDwsBillingStatementJobInteractor implements RunRefreshDwsBillingStatementJobUseCase
{
    private RunJobUseCase $runJobUseCase;
    private RefreshDwsBillingStatementUseCase $useCase;

    /**
     * Constructor.
     *
     * @param \UseCase\Job\RunJobUseCase $runJobUseCase
     * @param \UseCase\Billing\RefreshDwsBillingStatementUseCase $useCase
     */
    public function __construct(
        RunJobUseCase $runJobUseCase,
        RefreshDwsBillingStatementUseCase $useCase
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
