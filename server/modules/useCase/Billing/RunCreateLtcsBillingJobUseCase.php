<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Common\Carbon;
use Domain\Common\CarbonRange;
use Domain\Context\Context;
use Domain\Job\Job as DomainJob;

/**
 * 介護保険サービス：請求生成ジョブ実行ユースケース.
 */
interface RunCreateLtcsBillingJobUseCase
{
    /**
     * 介護保険サービス：請求生成ジョブを実行する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Job\Job $domainJob
     * @param int $officeId 事業所ID
     * @param \Domain\Common\Carbon $transactedIn 処理年月
     * @param \Domain\Common\CarbonRange $fixedAt 対象期間
     */
    public function handle(Context $context, DomainJob $domainJob, int $officeId, Carbon $transactedIn, CarbonRange $fixedAt): void;
}
