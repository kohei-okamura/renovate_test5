<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Job;

use Domain\Context\Context;
use ScalikePHP\Seq;

/**
 * ジョブ情報取得ユースケース.
 */
interface LookupJobUseCase
{
    /**
     * ID を指定して ジョブ情報を取得する.
     *
     * @param \Domain\Context\Context $context
     * @param int ...$id
     * @return \Domain\Job\Job[]|\ScalikePHP\Seq
     */
    public function handle(Context $context, int ...$id): Seq;
}
