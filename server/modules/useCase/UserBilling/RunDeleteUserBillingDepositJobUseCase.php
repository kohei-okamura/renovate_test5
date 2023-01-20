<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\UserBilling;

use Domain\Context\Context;
use Domain\Job\Job as DomainJob;

/**
 * 利用者請求入金日削除ジョブ実行ユースケース.
 */
interface RunDeleteUserBillingDepositJobUseCase
{
    /**
     * 利用者請求入金日削除ジョブを実行する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Job\Job $domainJob
     * @param int[] $ids
     * @return void
     */
    public function handle(Context $context, DomainJob $domainJob, int ...$ids): void;
}
