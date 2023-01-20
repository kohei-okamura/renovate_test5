<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Staff;

use Domain\Context\Context;
use Domain\Staff\StaffPasswordReset;

/**
 * スタッフパスワード再設定取得ユースケース.
 */
interface GetStaffPasswordResetUseCase
{
    /**
     * スタッフパスワード再設定を取得する.
     *
     * @param \Domain\Context\Context $context
     * @param string $token
     * @return \Domain\Staff\StaffPasswordReset
     */
    public function handle(Context $context, string $token): StaffPasswordReset;
}
