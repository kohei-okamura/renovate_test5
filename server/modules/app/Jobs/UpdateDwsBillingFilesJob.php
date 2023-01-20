<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Jobs;

use Domain\Context\Context;
use Domain\Job\Job as DomainJob;
use UseCase\Billing\RunUpdateDwsBillingFilesJobUseCase;

/**
 * 障害福祉サービス：請求：ファイル生成ジョブ.
 */
class UpdateDwsBillingFilesJob extends Job
{
    public $timeout = 3600;
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
     * 障害福祉サービス：請求：ファイル生成ジョブを実行する.
     *
     * @param \UseCase\Billing\RunUpdateDwsBillingFilesJobUseCase $useCase
     */
    public function handle(RunUpdateDwsBillingFilesJobUseCase $useCase): void
    {
        $useCase->handle(
            $this->context,
            $this->domainJob,
            $this->billingId
        );
    }
}
