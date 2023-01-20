<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Jobs;

use Domain\Context\Context;
use Domain\Job\Job as DomainJob;
use UseCase\UserBilling\RunImportWithdrawalTransactionFileJobUseCase;

/**
 * 全銀ファイルアップロードジョブ.
 */
final class ImportWithdrawalTransactionFileJob
{
    private Context $context;
    private string $path;
    private DomainJob $domainJob;

    /**
     * Constructor.
     *
     * @param \Domain\Context\Context $context
     * @param string $path
     * @param \Domain\Job\Job $domainJob
     */
    public function __construct(Context $context, string $path, DomainJob $domainJob)
    {
        $this->context = $context;
        $this->path = $path;
        $this->domainJob = $domainJob;
    }

    /**
     * 全銀ファイルをアップロードする.
     *
     * @param \UseCase\UserBilling\RunImportWithdrawalTransactionFileJobUseCase $useCase
     * @return void
     */
    public function handle(RunImportWithdrawalTransactionFileJobUseCase $useCase): void
    {
        $useCase->handle($this->context, $this->path, $this->domainJob);
    }
}
