<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Jobs;

use Domain\Context\Context;
use Domain\Job\Job as DomainJob;
use UseCase\Shift\RunConfirmShiftJobUseCase;

/**
 * 勤務シフト一括確定ジョブ.
 */
final class ConfirmShiftJob extends Job
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
     * 勤務シフト一括確定ジョブを実行する.
     *
     * @param \UseCase\Shift\RunConfirmShiftJobUseCase $useCase
     * @return void
     */
    public function handle(RunConfirmShiftJobUseCase $useCase): void
    {
        $useCase->handle($this->context, $this->domainJob, $this->ids);
    }
}
