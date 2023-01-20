<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\UserBilling;

use Domain\Common\Carbon;
use Domain\Context\Context;
use Domain\Job\Job as DomainJob;

/**
 * 利用者請求入金日更新ジョブ実行ユースケース.
 */
interface RunUpdateUserBillingDepositJobUseCase
{
    /**
     * 利用者請求入金日更新ジョブを実行する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Job\Job $domainJob
     * @param \Domain\Common\Carbon $depositedAt
     * @param array $ids
     * @return void
     */
    public function handle(Context $context, DomainJob $domainJob, Carbon $depositedAt, array $ids): void;
}
