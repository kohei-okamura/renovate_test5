<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Shift;

use Domain\Context\Context;
use Domain\Job\Job as DomainJob;
use UseCase\Job\RunJobUseCase;

/**
 * 勤務実績一括確定ジョブ実行ユースケース実装.
 */
final class RunConfirmAttendanceJobInteractor implements RunConfirmAttendanceJobUseCase
{
    private RunJobUseCase $runJobUseCase;
    private ConfirmAttendanceUseCase $confirmAttendanceUseCase;

    /**
     * Constructor.
     *
     * @param \UseCase\Job\RunJobUseCase $runJobUseCase
     * @param \UseCase\Shift\ConfirmAttendanceUseCase $confirmAttendanceUseCase
     */
    public function __construct(
        RunJobUseCase $runJobUseCase,
        ConfirmAttendanceUseCase $confirmAttendanceUseCase
    ) {
        $this->runJobUseCase = $runJobUseCase;
        $this->confirmAttendanceUseCase = $confirmAttendanceUseCase;
    }

    /** {@inheritdoc} */
    public function handle(Context $context, DomainJob $domainJob, array $ids): void
    {
        $this->runJobUseCase->handle(
            $context,
            $domainJob,
            function () use ($context, $ids): void {
                $this->confirmAttendanceUseCase->handle($context, ...$ids);
            }
        );
    }
}
