<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Jobs;

use Domain\Context\Context;
use Domain\Job\Job as DomainJob;
use UseCase\Billing\RunRefreshLtcsBillingStatementJobUseCase;

/**
 * 介護保険サービス：明細書リフレッシュジョブ.
 */
final class RefreshLtcsBillingStatementJob extends Job
{
    public int $timeout = 3600;
    private Context $context;
    private DomainJob $domainJob;
    private int $billingId;
    private array $ids;

    /**
     * Constructor.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Job\Job $domainJob
     * @param int $billingId
     * @param array $ids
     */
    public function __construct(
        Context $context,
        DomainJob $domainJob,
        int $billingId,
        array $ids
    ) {
        $this->context = $context;
        $this->domainJob = $domainJob;
        $this->billingId = $billingId;
        $this->ids = $ids;
    }

    /**
     * 介護保険サービス：明細書リフレッシュジョブを実行する.
     *
     * @param \UseCase\Billing\RunRefreshLtcsBillingStatementJobUseCase $useCase
     * @return void
     */
    public function handle(RunRefreshLtcsBillingStatementJobUseCase $useCase): void
    {
        $useCase->handle(
            $this->context,
            $this->domainJob,
            $this->billingId,
            $this->ids,
        );
    }
}
