<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Context\Context;
use Domain\Job\Job as DomainJob;

/**
 * 介護保険サービス：明細書リフレッシュジョブ実行ユースケース.
 */
interface RunRefreshLtcsBillingStatementJobUseCase
{
    /**
     * 介護保険サービス：明細書リフレッシュジョブを実行する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Job\Job $domainJob
     * @param int $billingId
     * @param array $ids
     * @return void
     */
    public function handle(
        Context $context,
        DomainJob $domainJob,
        int $billingId,
        array $ids
    ): void;
}
