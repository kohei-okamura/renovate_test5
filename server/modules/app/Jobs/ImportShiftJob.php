<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Jobs;

use Domain\Context\Context;
use Domain\Job\Job as DomainJob;
use UseCase\Shift\RunImportShiftJobUseCase;

/**
 * ImportShiftJob
 */
final class ImportShiftJob extends Job
{
    private Context $context;
    private string $path;
    private DomainJob $domainJob;

    /**
     * Constructor.
     *
     * @param \Domain\Context\Context $context
     * @param string $path
     * @param \Domain\Job\Job $domainJob
     */
    public function __construct(Context $context, string $path, DomainJob $domainJob)
    {
        $this->context = $context;
        $this->path = $path;
        $this->domainJob = $domainJob;
    }

    /**
     * 勤務シフト雛形を生成する.
     *
     * @param \UseCase\Shift\RunImportShiftJobUseCase $useCase
     * @return void
     */
    public function handle(RunImportShiftJobUseCase $useCase): void
    {
        $useCase->handle($this->context, $this->path, $this->domainJob);
    }
}
