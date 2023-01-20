<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Job;

use Domain\Context\Context;

/**
 * ジョブ開始ユースケース.
 */
interface StartJobUseCase
{
    /**
     * ジョブを開始する.
     *
     * @param \Domain\Context\Context $context
     * @param int $id
     * @return void
     */
    public function handle(Context $context, int $id): void;
}
