<?php
/**
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Jobs;

use Domain\Context\Context;
use Domain\Job\Job as DomainJob;
use UseCase\Billing\RunCopyDwsBillingJobUseCase;

/**
 * 障害福祉サービス：請求コピージョブ.
 */
final class CopyDwsBillingJob extends Job
{
    public int $timeout = 3600;
    private Context $context;
    private DomainJob $domainJob;
    private int $billingId;

    /**
     * constructor.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Job\Job $domainJob
     * @param int $billingId
     */
    public function __construct(
        Context $context,
        DomainJob $domainJob,
        int $billingId
    ) {
        $this->context = $context;
        $this->domainJob = $domainJob;
        $this->billingId = $billingId;
    }

    /**
     * 障害福祉サービス：請求コピージョブを実行する.
     *
     * @param \UseCase\Billing\RunCopyDwsBillingJobUseCase $useCase
     */
    public function handle(RunCopyDwsBillingJobUseCase $useCase): void
    {
        $useCase->handle(
            $this->context,
            $this->domainJob,
            $this->billingId
        );
    }
}
