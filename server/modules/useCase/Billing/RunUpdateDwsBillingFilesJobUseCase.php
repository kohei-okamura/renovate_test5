<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Context\Context;
use Domain\Job\Job as DomainJob;

/**
 * 障害福祉サービス：請求：ファイル生成ジョブ実行ユースケース.
 */
interface RunUpdateDwsBillingFilesJobUseCase
{
    /**
     * 障害福祉サービス：請求：ファイル生成ジョブの実行.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Job\Job $job
     * @param int $billingId 請求ID
     */
    public function handle(Context $context, DomainJob $job, int $billingId): void;
}
