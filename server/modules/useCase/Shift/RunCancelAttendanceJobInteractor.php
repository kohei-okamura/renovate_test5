<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Shift;

use Domain\Context\Context;
use Domain\Job\Job as DomainJob;
use UseCase\Job\RunJobUseCase;

/**
 * 勤務実績一括キャンセルジョブ実行ユースケース実装.
 */
final class RunCancelAttendanceJobInteractor implements RunCancelAttendanceJobUseCase
{
    private RunJobUseCase $runJobUseCase;
    private CancelAttendanceUseCase $cancelAttendanceUseCase;

    /**
     * Constructor.
     *
     * @param \UseCase\Job\RunJobUseCase $runJobUseCase
     * @param \UseCase\Shift\CancelAttendanceUseCase $cancelAttendanceUseCase
     */
    public function __construct(RunJobUseCase $runJobUseCase, CancelAttendanceUseCase $cancelAttendanceUseCase)
    {
        $this->runJobUseCase = $runJobUseCase;
        $this->cancelAttendanceUseCase = $cancelAttendanceUseCase;
    }

    /** {@inheritdoc} */
    public function handle(Context $context, DomainJob $domainJob, string $reason, int ...$ids): void
    {
        $this->runJobUseCase->handle(
            $context,
            $domainJob,
            function () use ($context, $reason, $ids): void {
                $this->cancelAttendanceUseCase->handle($context, $reason, ...$ids);
            }
        );
    }
}
