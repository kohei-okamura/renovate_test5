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
 * ジョブ編集ユースケース.
 */
interface EditJobUseCase
{
    /**
     * ジョブを編集する.
     *
     * @param \Domain\Context\Context $context
     * @param int $id
     * @param array $values
     * @return \Domain\Job\Job
     */
    public function handle(Context $context, int $id, array $values): Job;
}
