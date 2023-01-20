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
 * 勤務シフト一括確定ジョブ実行ユースケース実装.
 */
final class RunConfirmShiftJobInteractor implements RunConfirmShiftJobUseCase
{
    private RunJobUseCase $runJobUseCase;
    private ConfirmShiftUseCase $confirmShiftUseCase;

    /**
     * Constructor.
     *
     * @param \UseCase\Job\RunJobUseCase $runJobUseCase
     * @param \UseCase\Shift\ConfirmShiftUseCase $confirmShiftUseCase
     */
    public function __construct(
        RunJobUseCase $runJobUseCase,
        ConfirmShiftUseCase $confirmShiftUseCase
    ) {
        $this->runJobUseCase = $runJobUseCase;
        $this->confirmShiftUseCase = $confirmShiftUseCase;
    }

    /** {@inheritdoc} */
    public function handle(Context $context, DomainJob $domainJob, array $ids): void
    {
        $this->runJobUseCase->handle(
            $context,
            $domainJob,
            function () use ($context, $ids): void {
                $this->confirmShiftUseCase->handle($context, ...$ids);
            }
        );
    }
}
