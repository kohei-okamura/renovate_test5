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
 * 勤務シフト一括登録ジョブ実行ユースケース実装.
 */
final class RunImportShiftJobInteractor implements RunImportShiftJobUseCase
{
    private RunJobUseCase $runJobUseCase;
    private ImportShiftUseCase $importShiftUseCase;

    /**
     * Constructor.
     *
     * @param \UseCase\Job\RunJobUseCase $runJobUseCase
     * @param \UseCase\Shift\ImportShiftUseCase $importShiftUseCase
     */
    public function __construct(RunJobUseCase $runJobUseCase, ImportShiftUseCase $importShiftUseCase)
    {
        $this->runJobUseCase = $runJobUseCase;
        $this->importShiftUseCase = $importShiftUseCase;
    }

    /** {@inheritdoc} */
    public function handle(Context $context, string $path, DomainJob $domainJob): void
    {
        $this->runJobUseCase->handle(
            $context,
            $domainJob,
            function () use ($context, $path): void {
                $this->importShiftUseCase->handle($context, $path);
            }
        );
    }
}
