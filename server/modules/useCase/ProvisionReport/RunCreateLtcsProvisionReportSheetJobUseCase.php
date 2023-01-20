<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\ProvisionReport;

use Domain\Common\Carbon;
use Domain\Context\Context;
use Domain\Job\Job as DomainJob;

/**
 * 介護保険サービス：サービス提供票生成ジョブ実行ユースケース.
 */
interface RunCreateLtcsProvisionReportSheetJobUseCase
{
    /**
     * 利用者請求：請求書生成ジョブを実行する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Job\Job $domainJob
     * @param int $officeId
     * @param int $userId
     * @param \Domain\Common\Carbon $providedIn
     * @param \Domain\Common\Carbon $issuedOn
     * @param bool $needsMaskingInsNumber
     * @param bool $needsMaskingInsName
     * @return void
     */
    public function handle(
        Context $context,
        DomainJob $domainJob,
        int $officeId,
        int $userId,
        Carbon $providedIn,
        Carbon $issuedOn,
        bool $needsMaskingInsNumber,
        bool $needsMaskingInsName
    ): void;
}
