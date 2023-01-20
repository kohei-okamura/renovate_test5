<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Jobs;

use Domain\Common\Carbon;
use Domain\Context\Context;
use Domain\Job\Job as DomainJob;
use UseCase\UserBilling\RunUpdateUserBillingDepositJobUseCase;

/**
 * 利用者請求入金日更新ジョブ.
 */
final class UpdateUserBillingDepositJob extends Job
{
    private Context $context;
    private DomainJob $domainJob;
    private Carbon $depositedAt;
    private array $ids;

    /**
     * Constructor.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Job\Job $domainJob
     * @param \Domain\Common\Carbon $depositedAt
     * @param array $ids
     */
    public function __construct(Context $context, DomainJob $domainJob, Carbon $depositedAt, array $ids)
    {
        $this->context = $context;
        $this->domainJob = $domainJob;
        $this->depositedAt = $depositedAt;
        $this->ids = $ids;
    }

    /**
     * 利用者請求入金日更新ジョブを実行する.
     *
     * @param \UseCase\UserBilling\RunUpdateUserBillingDepositJobUseCase $useCase
     * @return void
     */
    public function handle(RunUpdateUserBillingDepositJobUseCase $useCase): void
    {
        $useCase->handle($this->context, $this->domainJob, $this->depositedAt, $this->ids);
    }
}
