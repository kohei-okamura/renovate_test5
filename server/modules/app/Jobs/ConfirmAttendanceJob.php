<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Jobs;

use Domain\Context\Context;
use Domain\Job\Job as DomainJob;
use UseCase\Shift\RunConfirmAttendanceJobUseCase;

/**
 * 勤務実績一括確定ジョブ.
 */
final class ConfirmAttendanceJob extends Job
{
    private Context $context;
    private DomainJob $domainJob;
    private array $ids;

    /**
     * Constructor.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Job\Job $domainJob
     * @param int[] $ids
     */
    public function __construct(Context $context, DomainJob $domainJob, array $ids)
    {
        $this->context = $context;
        $this->domainJob = $domainJob;
        $this->ids = $ids;
    }

    /**
     * 勤務実績一括確定ジョブを実行する.
     *
     * @param \UseCase\Shift\RunConfirmAttendanceJobUseCase $useCase
     * @return void
     */
    public function handle(RunConfirmAttendanceJobUseCase $useCase): void
    {
        $useCase->handle($this->context, $this->domainJob, $this->ids);
    }
}
