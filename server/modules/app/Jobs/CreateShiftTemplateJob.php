<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Jobs;

use Domain\Context\Context;
use Domain\Job\Job as DomainJob;
use UseCase\Shift\RunCreateShiftTemplateJobUseCase;

/**
 * 勤務シフト雛形作成ジョブ.
 */
final class CreateShiftTemplateJob extends Job
{
    private Context $context;
    private DomainJob $domainJob;
    private array $parameters;

    /**
     * Constructor.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Job\Job $domainJob
     * @param array $parameters
     */
    public function __construct(Context $context, DomainJob $domainJob, array $parameters)
    {
        $this->context = $context;
        $this->domainJob = $domainJob;
        $this->parameters = $parameters;
    }

    /**
     * 勤務シフト雛形生成ジョブを実行する.
     *
     * @param \UseCase\Shift\RunCreateShiftTemplateJobUseCase $useCase
     * @return void
     */
    public function handle(RunCreateShiftTemplateJobUseCase $useCase): void
    {
        $useCase->handle($this->context, $this->domainJob, $this->parameters);
    }
}
