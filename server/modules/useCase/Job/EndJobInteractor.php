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
 * ジョブ終了実装.
 */
final class EndJobInteractor implements EndJobUseCase
{
    private JobRepository $repository;
    private EditJobUseCase $editJobUseCase;

    /**
     * Constructor.
     *
     * @param \Domain\Job\JobRepository $repository
     * @param \UseCase\Job\EditJobUseCase $editJobUseCase
     */
    public function __construct(
        JobRepository $repository,
        EditJobUseCase $editJobUseCase
    ) {
        $this->repository = $repository;
        $this->editJobUseCase = $editJobUseCase;
    }

    /** {@inheritdoc} */
    public function handle(Context $context, int $id, JobStatus $status, array $data = null): void
    {
        $values = [
            'status' => $status,
            'data' => $data,
            'updatedAt' => Carbon::now(),
        ];
        $this->editJobUseCase->handle($context, $id, $values);
    }
}
