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
use UseCase\UserBilling\RunCreateUserBillingStatementJobUseCase;

/**
 * 利用者請求：介護サービス利用明細書生成ジョブ.
 */
class CreateUserBillingStatementJob extends Job
{
    private Context $context;
    private DomainJob $domainJob;
    private array $ids;
    private Carbon $issuedOn;

    /**
     * constructor.
     *
     * @param \Domain\Context\Context $context
     * @param DomainJob $domainJob
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
     * 利用者請求：介護サービス利用明細書生成ジョブを実行する.
     *
     * @param \UseCase\UserBilling\RunCreateUserBillingStatementJobUseCase $useCase
     * @return void
     */
    public function handle(RunCreateUserBillingStatementJobUseCase $useCase): void
    {
        $useCase->handle($this->context, $this->domainJob, $this->ids, $this->issuedOn);
    }
}
