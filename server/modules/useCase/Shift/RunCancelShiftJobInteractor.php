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
 * 勤務シフトキャンセルジョブ実行ユースケース実装.
 */
final class RunCancelShiftJobInteractor implements RunCancelShiftJobUseCase
{
    private RunJobUseCase $runJobUseCase;
    private CancelShiftUseCase $cancelShiftUseCase;

    /**
     * Constructor.
     *
     * @param \UseCase\Job\RunJobUseCase $runJobUseCase
     * @param \UseCase\Shift\CancelShiftUseCase $cancelShiftUseCase
     */
    public function __construct(
        RunJobUseCase $runJobUseCase,
        CancelShiftUseCase $cancelShiftUseCase
    ) {
        $this->runJobUseCase = $runJobUseCase;
        $this->cancelShiftUseCase = $cancelShiftUseCase;
    }

    /** {@inheritdoc} */
    public function handle(Context $context, DomainJob $domainJob, string $reason, int ...$ids): void
    {
        $this->runJobUseCase->handle(
            $context,
            $domainJob,
            function () use ($context, $reason, $ids): void {
                $this->cancelShiftUseCase->handle($context, $reason, ...$ids);
            }
        );
    }
}
