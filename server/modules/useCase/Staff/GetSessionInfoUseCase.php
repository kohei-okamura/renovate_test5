<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Staff;

use Domain\Context\Context;
use ScalikePHP\Option;

/**
 * Get session info use case.
 */
interface GetSessionInfoUseCase
{
    /**
     * セッション情報を取得する.
     *
     * @param \Domain\Context\Context $context
     * @return array|\ScalikePHP\Option
     */
    public function handle(Context $context): Option;
}
