<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Jobs;

use Domain\Common\Carbon;
use Domain\Context\Context;
use Domain\Job\Job as DomainJob;
use UseCase\UserBilling\RunCreateUserBillingInvoiceJobUseCase;

/**
 * 利用者請求：請求書作成ジョブ.
 */
final class CreateUserBillingInvoiceJob extends Job
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
     * @param int[] $ids
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
     * 利用者請求：請求書生成ジョブを実行する.
     *
     * @param \UseCase\UserBilling\RunCreateUserBillingInvoiceJobUseCase $useCase
     * @return void
     */
    public function handle(RunCreateUserBillingInvoiceJobUseCase $useCase): void
    {
        $useCase->handle($this->context, $this->domainJob, $this->ids, $this->issuedOn);
    }
}
