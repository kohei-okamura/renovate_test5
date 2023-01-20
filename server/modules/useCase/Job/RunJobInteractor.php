<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Job;

use Domain\Context\Context;
use Domain\Job\Job;
use Domain\Job\JobStatus;
use Lib\Exceptions\ValidationException;
use Throwable;

/**
 * ジョブ実行ユースケース実装.
 */
class RunJobInteractor implements RunJobUseCase
{
    private StartJobUseCase $startJobUseCase;
    private EndJobUseCase $endJobUseCase;

    /**
     * {@link \UseCase\Job\RunJobInteractor} constructor.
     *
     * @param \UseCase\Job\StartJobUseCase $startJobUseCase
     * @param \UseCase\Job\EndJobUseCase $endJobUseCase
     */
    public function __construct(StartJobUseCase $startJobUseCase, EndJobUseCase $endJobUseCase)
    {
        $this->startJobUseCase = $startJobUseCase;
        $this->endJobUseCase = $endJobUseCase;
    }

    /** {@inheritdoc} */
    public function handle(Context $context, Job $job, callable $f): void
    {
        $this->startJobUseCase->handle($context, $job->id);
        try {
            $data = $f();
            $this->endJobUseCase->handle($context, $job->id, JobStatus::success(), $data);
        } catch (ValidationException $exception) {
            $this->endJobUseCase->handle($context, $job->id, JobStatus::failure(), ['error' => $exception->getErrors()]);
            // ValidationExceptionはエラーログの記録はしない(throwしない）
        } catch (Throwable $e) {
            $this->endJobUseCase->handle($context, $job->id, JobStatus::failure(), ['failure' => $e->getMessage() . $e->getTraceAsString()]);
            throw $e;
        }
    }
}
