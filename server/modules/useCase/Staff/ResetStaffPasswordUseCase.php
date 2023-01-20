<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Staff;

use Domain\Context\Context;

/**
 * スタッフパスワード再設定ユースケース.
 */
interface ResetStaffPasswordUseCase
{
    /**
     * スタッフのパスワードを再設定する.
     *
     * @param \Domain\Context\Context $context
     * @param string $token
     * @param string $password
     * @return void
     */
    public function handle(Context $context, string $token, string $password): void;
}
