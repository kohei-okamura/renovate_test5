<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Job;

use Domain\Context\Context;
use Domain\Job\Job;

/**
 * ジョブ実行ユースケース.
 */
interface RunJobUseCase
{
    /**
     * ジョブを実行する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Job\Job $job
     * @param callable $f
     */
    public function handle(Context $context, Job $job, callable $f): void;
}
