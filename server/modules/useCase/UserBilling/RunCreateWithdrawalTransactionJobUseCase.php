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
 * 口座振替データ作成ジョブ実行ユースケース.
 */
interface RunCreateWithdrawalTransactionJobUseCase
{
    /**
     * 口座振替データ作成ジョブを実行する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Job\Job $domainJob
     * @param array $userBillingIds
     * @return void
     */
    public function handle(Context $context, DomainJob $domainJob, array $userBillingIds): void;
}
