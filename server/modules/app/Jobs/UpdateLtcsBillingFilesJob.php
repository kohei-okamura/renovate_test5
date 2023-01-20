<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Jobs;

use Domain\Context\Context;
use Domain\Job\Job as DomainJob;
use UseCase\Billing\RunUpdateLtcsBillingFilesJobUseCase;

/**
 * 介護保険サービス：請求：ファイル生成ジョブ.
 */
class UpdateLtcsBillingFilesJob extends Job
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
     * 介護保険サービス：請求：ファイル生成ジョブを実行する.
     *
     * @param \UseCase\Billing\RunUpdateLtcsBillingFilesJobUseCase $useCase
     */
    public function handle(RunUpdateLtcsBillingFilesJobUseCase $useCase): void
    {
        $useCase->handle(
            $this->context,
            $this->domainJob,
            $this->billingId
        );
    }
}
