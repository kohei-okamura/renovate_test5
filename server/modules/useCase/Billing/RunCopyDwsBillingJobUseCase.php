<?php
/**
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Context\Context;
use Domain\Job\Job as DomainJob;

/**
 * 障害福祉サービス：請求コピージョブ実行ユースケース.
 */
interface RunCopyDwsBillingJobUseCase
{
    /**
     * 障害福祉サービス：請求コピージョブを実行する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Job\Job $domainJob
     * @param int $billingId 請求ID
     */
    public function handle(Context $context, DomainJob $domainJob, int $billingId): void;
}
