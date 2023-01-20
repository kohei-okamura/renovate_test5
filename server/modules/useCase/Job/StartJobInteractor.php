<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Job;

use Domain\Common\Carbon;
use Domain\Context\Context;
use Domain\Job\JobRepository;
use Domain\Job\JobStatus;

/**
 * ジョブ開始実装.
 */
final class StartJobInteractor implements StartJobUseCase
{
    private JobRepository $jobRepository;
    private EditJobUseCase $editJobUseCase;

    /**
     * Constructor.
     *
     * @param \Domain\Job\JobRepository $jobRepository
     * @param \UseCase\Job\EditJobUseCase $editJobUseCase
     */
    public function __construct(
        JobRepository $jobRepository,
        EditJobUseCase $editJobUseCase
    ) {
        $this->jobRepository = $jobRepository;
        $this->editJobUseCase = $editJobUseCase;
    }

    /** {@inheritdoc} */
    public function handle(Context $context, int $id): void
    {
        $values = [
            'status' => JobStatus::inProgress(),
            'updatedAt' => Carbon::now(),
        ];
        $this->editJobUseCase->handle($context, $id, $values);
    }
}
