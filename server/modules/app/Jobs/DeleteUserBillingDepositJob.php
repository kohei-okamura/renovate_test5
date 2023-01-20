<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Jobs;

use Domain\Context\Context;
use Domain\Job\Job as DomainJob;
use UseCase\UserBilling\RunDeleteUserBillingDepositJobUseCase;

/**
 * 利用者請求入金日削除ジョブ.
 */
final class DeleteUserBillingDepositJob extends Job
{
    private Context $context;
    private DomainJob $domainJob;
    private array $ids;

    /**
     * Constructor.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Job\Job $domainJob
     * @param int[] $ids
     */
    public function __construct(Context $context, DomainJob $domainJob, int ...$ids)
    {
        $this->context = $context;
        $this->domainJob = $domainJob;
        $this->ids = $ids;
    }

    /**
     * 利用者請求入金日削除ジョブを実行する.
     *
     * @param \UseCase\UserBilling\RunDeleteUserBillingDepositJobUseCase $useCase
     * @return void
     */
    public function handle(RunDeleteUserBillingDepositJobUseCase $useCase): void
    {
        $useCase->handle($this->context, $this->domainJob, ...$this->ids);
    }
}
