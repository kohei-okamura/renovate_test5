<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Job;

use Domain\Context\Context;
use Domain\Job\JobStatus;

/**
 * ジョブ終了ユースケース.
 */
interface EndJobUseCase
{
    /**
     * ジョブを終了する.
     *
     * @param \Domain\Context\Context $context
     * @param int $id
     * @param \Domain\Job\JobStatus $status
     * @param array $data
     * @return void
     */
    public function handle(Context $context, int $id, JobStatus $status, array $data): void;
}
