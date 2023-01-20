<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Billing\DwsBillingStatus;
use Domain\Context\Context;
use Domain\Job\Job as DomainJob;

/**
 * 障害福祉サービス：明細書状態一括更新ジョブ実行ユースケース.
 */
interface RunBulkUpdateDwsBillingStatementStatusJobUseCase
{
    /**
     * 障害福祉サービス：明細書状態一括更新ジョブを実行する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Job\Job $domainJob
     * @param int $billingId
     * @param array $ids
     * @param \Domain\Billing\DwsBillingStatus $status
     * @return void
     */
    public function handle(
        Context $context,
        DomainJob $domainJob,
        int $billingId,
        array $ids,
        DwsBillingStatus $status
    ): void;
}
