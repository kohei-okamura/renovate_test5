<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Staff;

use Domain\Context\Context;
use ScalikePHP\Option;

/**
 * ログアウトユースケース.
 */
interface StaffLoggedOutUseCase
{
    /**
     * ログアウトイベントをディスパッチする.
     *
     * @param \Domain\Context\Context $context
     * @param \ScalikePHP\Option $rememberTokenId
     * @return void
     */
    public function handle(Context $context, Option $rememberTokenId): void;
}
