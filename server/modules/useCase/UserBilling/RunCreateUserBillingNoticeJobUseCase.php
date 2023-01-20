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
 * 代理受領額通知書生成ジョブ実行ユースケース.
 */
interface RunCreateUserBillingNoticeJobUseCase
{
    /**
     * 代理受領額通知書生成ジョブを実行する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Job\Job $domainJob
     * @param array $ids
     * @param \Domain\Common\Carbon $issuedOn
     * @return void
     */
    public function handle(Context $context, DomainJob $domainJob, array $ids, Carbon $issuedOn): void;
}
