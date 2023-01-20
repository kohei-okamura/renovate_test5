<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Jobs;

use Domain\Common\Carbon;
use Domain\Common\CarbonRange;
use Domain\Context\Context;
use Domain\Job\Job as DomainJob;
use UseCase\Billing\RunCreateDwsBillingJobUseCase;

/**
 * 障害福祉サービス：請求生成ジョブ.
 */
final class CreateDwsBillingJob extends Job
{
    public int $timeout = 3600;
    private Context $context;
    private DomainJob $domainJob;
    private int $officeId;
    private Carbon $transactedIn;
    private CarbonRange $fixedAt;

    /**
     * constructor.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Job\Job $domainJob
     * @param int $officeId
     * @param \Domain\Common\Carbon $transactedIn
     * @param \Domain\Common\CarbonRange $fixedAt
     */
    public function __construct(
        Context $context,
        DomainJob $domainJob,
        int $officeId,
        Carbon $transactedIn,
        CarbonRange $fixedAt
    ) {
        $this->context = $context;
        $this->domainJob = $domainJob;
        $this->officeId = $officeId;
        $this->transactedIn = $transactedIn;
        $this->fixedAt = $fixedAt;
    }

    /**
     * 障害福祉サービス：請求生成ジョブを実行する.
     *
     * @param \UseCase\Billing\RunCreateDwsBillingJobUseCase $useCase
     */
    public function handle(RunCreateDwsBillingJobUseCase $useCase): void
    {
        $useCase->handle(
            $this->context,
            $this->domainJob,
            $this->officeId,
            $this->transactedIn,
            $this->fixedAt
        );
    }
}
