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
 * トークンによるジョブ情報取得ユースケース.
 */
interface LookupJobByTokenUseCase
{
    /**
     * ジョブ情報を取得する.
     *
     * @param \Domain\Context\Context $context
     * @param string $token
     * @return \Domain\Job\Job[]|\ScalikePHP\Seq
     */
    public function handle(Context $context, string $token): Seq;
}
