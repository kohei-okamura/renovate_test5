<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\ProvisionReport;

use Domain\Common\Carbon;
use Domain\Context\Context;
use Domain\Job\Job as DomainJob;

/**
 * サービス提供実績記録票（プレビュー版）生成ジョブ実行ユースケース.
 */
interface RunCreateDwsServiceReportPreviewJobUseCase
{
    /**
     * サービス提供実績記録票（プレビュー版）生成ジョブを実行する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Job\Job $domainJob
     * @param int $officeId
     * @param int $userId
     * @param \Domain\Common\Carbon $providedIn
     * @return void
     */
    public function handle(
        Context $context,
        DomainJob $domainJob,
        int $officeId,
        int $userId,
        Carbon $providedIn,
    ): void;
}
