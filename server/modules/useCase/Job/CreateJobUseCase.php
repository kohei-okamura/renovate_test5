<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Job;

use Domain\Context\Context;
use Domain\Job\Job;

/**
 * ジョブ登録ユースケース.
 */
interface CreateJobUseCase
{
    /**
     * ジョブを登録する.
     *
     * @param \Domain\Context\Context $context
     * @param callable $f
     * @return \Domain\Job\Job
     */
    public function handle(Context $context, callable $f): Job;
}
