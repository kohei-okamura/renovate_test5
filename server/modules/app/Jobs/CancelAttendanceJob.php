<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Jobs;

use Domain\Context\Context;
use Domain\Job\Job as DomainJob;
use UseCase\Shift\RunCancelAttendanceJobUseCase;

/**
 * 勤務実績キャンセルジョブ.
 */
final class CancelAttendanceJob extends Job
{
    private Context $context;
    private DomainJob $domainJob;
    private string $reason;
    private array $ids;

    /**
     * Constructor.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Job\Job $domainJob
     * @param string $reason
     * @param int[] $ids
     */
    public function __construct(Context $context, DomainJob $domainJob, string $reason, int ...$ids)
    {
        $this->context = $context;
        $this->domainJob = $domainJob;
        $this->reason = $reason;
        $this->ids = $ids;
    }

    /**
     * 勤務勤務キャンセルジョブを実行する.
     *
     * @param \UseCase\Shift\RunCancelAttendanceJobUseCase $useCase
     * @return void
     */
    public function handle(RunCancelAttendanceJobUseCase $useCase): void
    {
        $useCase->handle($this->context, $this->domainJob, $this->reason, ...$this->ids);
    }
}
