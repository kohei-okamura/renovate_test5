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
use UseCase\UserBilling\RunCreateUserBillingNoticeJobUseCase;

/**
 * 代理受領額通知書生成ジョブ.
 */
class CreateUserBillingNoticeJob extends Job
{
    private Context $context;
    private DomainJob $domainJob;
    private array $ids;
    private Carbon $issuedOn;

    /**
     * Constructor.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Job\Job $domainJob
     * @param array|int[] $ids 利用者請求ID
     * @param \Domain\Common\Carbon $issuedOn
     */
    public function __construct(Context $context, DomainJob $domainJob, array $ids, Carbon $issuedOn)
    {
        $this->context = $context;
        $this->domainJob = $domainJob;
        $this->ids = $ids;
        $this->issuedOn = $issuedOn;
    }

    /**
     * 代理受領額通知書生成ジョブを実行する.
     *
     * @param \UseCase\UserBilling\RunCreateUserBillingNoticeJobUseCase $useCase
     * @return void
     */
    public function handle(RunCreateUserBillingNoticeJobUseCase $useCase): void
    {
        $useCase->handle($this->context, $this->domainJob, $this->ids, $this->issuedOn);
    }
}
