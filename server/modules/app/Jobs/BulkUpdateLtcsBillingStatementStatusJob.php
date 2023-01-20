<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Jobs;

use Domain\Billing\LtcsBillingStatus;
use Domain\Context\Context;
use Domain\Job\Job as DomainJob;
use UseCase\Billing\RunBulkUpdateLtcsBillingStatementStatusJobUseCase;

/**
 * 介護保険サービス：明細書状態一括更新ジョブ.
 */
final class BulkUpdateLtcsBillingStatementStatusJob extends Job
{
    private Context $context;
    private DomainJob $domainJob;
    private int $billingId;
    private int $bundleId;
    private array $ids;
    private LtcsBillingStatus $status;

    /**
     * Constructor.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Job\Job $domainJob
     * @param array $ids
     * @param \Domain\Billing\LtcsBillingStatus $status
     * @param int $billingId
     * @param int $bundleId
     */
    public function __construct(
        Context $context,
        DomainJob $domainJob,
        int $billingId,
        int $bundleId,
        array $ids,
        LtcsBillingStatus $status
    ) {
        $this->context = $context;
        $this->domainJob = $domainJob;
        $this->billingId = $billingId;
        $this->bundleId = $bundleId;
        $this->ids = $ids;
        $this->status = $status;
    }

    /**
     * 介護保険サービス：明細書状態一括更新ジョブを実行する.
     *
     * @param \UseCase\Billing\RunBulkUpdateLtcsBillingStatementStatusJobUseCase $useCase
     * @return void
     */
    public function handle(RunBulkUpdateLtcsBillingStatementStatusJobUseCase $useCase): void
    {
        $useCase->handle(
            $this->context,
            $this->domainJob,
            $this->billingId,
            $this->bundleId,
            $this->ids,
            $this->status
        );
    }
}
