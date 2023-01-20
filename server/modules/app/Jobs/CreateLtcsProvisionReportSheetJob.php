<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Jobs;

use Domain\Common\Carbon;
use Domain\Context\Context;
use Domain\Job\Job as DomainJob;
use UseCase\ProvisionReport\RunCreateLtcsProvisionReportSheetJobUseCase;

/**
 * 介護保険サービス：サービス提供票作成ジョブ.
 */
final class CreateLtcsProvisionReportSheetJob extends Job
{
    /**
     * Constructor.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Job\Job $domainJob
     * @param int $officeId
     * @param int $userId
     * @param \Domain\Common\Carbon $providedIn
     * @param \Domain\Common\Carbon $issuedOn
     * @param bool $needsMaskingInsNumber
     * @param bool $needsMaskingInsName
     */
    public function __construct(
        private Context $context,
        private DomainJob $domainJob,
        private int $officeId,
        private int $userId,
        private Carbon $providedIn,
        private Carbon $issuedOn,
        private bool $needsMaskingInsNumber,
        private bool $needsMaskingInsName
    ) {
    }

    /**
     * 介護保険サービス：サービス提供票生成ジョブを実行する.
     *
     * @param \UseCase\ProvisionReport\RunCreateLtcsProvisionReportSheetJobUseCase $useCase
     * @return void
     */
    public function handle(RunCreateLtcsProvisionReportSheetJobUseCase $useCase): void
    {
        $useCase->handle(
            $this->context,
            $this->domainJob,
            $this->officeId,
            $this->userId,
            $this->providedIn,
            $this->issuedOn,
            $this->needsMaskingInsNumber,
            $this->needsMaskingInsName,
        );
    }
}
