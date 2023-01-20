<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Jobs;

use Domain\Billing\DwsBillingStatus;
use Domain\Context\Context;
use Domain\Job\Job as DomainJob;
use UseCase\Billing\RunBulkUpdateDwsBillingServiceReportStatusJobUseCase;

/**
 * 障害福祉サービス：サービス提供実績記録票状態一括更新ジョブ.
 */
final class BulkUpdateDwsBillingServiceReportStatusJob extends Job
{
    private Context $context;
    private DomainJob $domainJob;
    private int $billingId;
    private array $ids;
    private DwsBillingStatus $status;

    /**
     * Constructor.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Job\Job $domainJob
     * @param array $ids
     * @param \Domain\Billing\DwsBillingStatus $status
     * @param int $billingId
     */
    public function __construct(
        Context $context,
        DomainJob $domainJob,
        int $billingId,
        array $ids,
        DwsBillingStatus $status
    ) {
        $this->context = $context;
        $this->domainJob = $domainJob;
        $this->billingId = $billingId;
        $this->ids = $ids;
        $this->status = $status;
    }

    /**
     * 障害福祉サービス：サービス提供実績記録票状態一括更新ジョブを実行する.
     *
     * @param \UseCase\Billing\RunBulkUpdateDwsBillingServiceReportStatusJobUseCase $useCase
     * @return void
     */
    public function handle(RunBulkUpdateDwsBillingServiceReportStatusJobUseCase $useCase): void
    {
        $useCase->handle(
            $this->context,
            $this->domainJob,
            $this->billingId,
            $this->ids,
            $this->status
        );
    }
}
