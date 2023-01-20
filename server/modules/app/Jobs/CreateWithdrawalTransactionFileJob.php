<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Jobs;

use Domain\Context\Context;
use Domain\Job\Job as DomainJob;
use UseCase\UserBilling\RunCreateWithdrawalTransactionFileJobUseCase;

/**
 * 全銀ファイル作成ジョブ.
 */
final class CreateWithdrawalTransactionFileJob extends Job
{
    private Context $context;
    private DomainJob $domainJob;
    private int $id;

    /**
     * Constructor.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Job\Job $domainJob
     * @param int $id
     */
    public function __construct(Context $context, DomainJob $domainJob, int $id)
    {
        $this->context = $context;
        $this->domainJob = $domainJob;
        $this->id = $id;
    }

    /**
     * 全銀ファイル作成ジョブを実行する.
     *
     * @param \UseCase\UserBilling\RunCreateWithdrawalTransactionFileJobUseCase $useCase
     * @return void
     */
    public function handle(RunCreateWithdrawalTransactionFileJobUseCase $useCase): void
    {
        $useCase->handle($this->context, $this->domainJob, $this->id);
    }
}
