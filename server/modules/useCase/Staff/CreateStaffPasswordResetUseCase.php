<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Staff;

use Domain\Context\Context;

/**
 * スタッフパスワード再設定登録ユースケース.
 */
interface CreateStaffPasswordResetUseCase
{
    /**
     * スタッフパスワード再設定を登録する.
     *
     * @param \Domain\Context\Context $context
     * @param string $email
     * @throws \Throwable
     * @return void
     */
    public function handle(Context $context, string $email): void;
}
