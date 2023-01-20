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
 * 利用者負担額一覧表作成ジョブ実行ユースケース.
 */
interface RunCreateCopayListJobUseCase
{
    /**
     * 利用者負担額一覧表作成ジョブを実行する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Job\Job $domainJob
     * @param int $billingId
     * @param array $ids
     * @param bool $isDivided
     * @return void
     */
    public function handle(
        Context $context,
        DomainJob $domainJob,
        int $billingId,
        array $ids,
        bool $isDivided
    ): void;
}
