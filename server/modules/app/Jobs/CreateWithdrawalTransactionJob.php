<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Jobs;

use Domain\Context\Context;
use Domain\Job\Job as DomainJob;
use UseCase\UserBilling\RunCreateWithdrawalTransactionJobUseCase;

/**
 * 口座振替データ作成ジョブ.
 */
final class CreateWithdrawalTransactionJob extends Job
{
    private Context $context;
    private DomainJob $domainJob;
    private array $userBillingIds;

    /**
     * Constructor.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Job\Job $domainJob
     * @param array $userBillingIds
     */
    public function __construct(Context $context, DomainJob $domainJob, array $userBillingIds)
    {
        $this->context = $context;
        $this->domainJob = $domainJob;
        $this->userBillingIds = $userBillingIds;
    }

    /**
     * 口座振替データ作成ジョブを実行する.
     *
     * @param \UseCase\UserBilling\RunCreateWithdrawalTransactionJobUseCase $useCase
     * @return void
     */
    public function handle(RunCreateWithdrawalTransactionJobUseCase $useCase): void
    {
        $useCase->handle($this->context, $this->domainJob, $this->userBillingIds);
    }
}
