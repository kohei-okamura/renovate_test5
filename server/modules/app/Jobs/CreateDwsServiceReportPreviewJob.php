<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Jobs;

use Domain\Common\Carbon;
use Domain\Context\Context;
use Domain\Job\Job as DomainJob;
use UseCase\ProvisionReport\RunCreateDwsServiceReportPreviewJobUseCase;

final class CreateDwsServiceReportPreviewJob extends Job
{
    /**
     * Constructor.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Job\Job $domainJob
     * @param int $officeId
     * @param int $userId
     * @param \Domain\Common\Carbon $providedIn
     */
    public function __construct(
        private Context $context,
        private DomainJob $domainJob,
        private int $officeId,
        private int $userId,
        private Carbon $providedIn,
    ) {
    }

    /**
     * サービス提供実績記録票（プレビュー版）生成ジョブを実行する.
     *
     * @param \UseCase\ProvisionReport\RunCreateDwsServiceReportPreviewJobUseCase $useCase
     * @return void
     */
    public function handle(RunCreateDwsServiceReportPreviewJobUseCase $useCase): void
    {
        $useCase->handle(
            $this->context,
            $this->domainJob,
            $this->officeId,
            $this->userId,
            $this->providedIn,
        );
    }
}
