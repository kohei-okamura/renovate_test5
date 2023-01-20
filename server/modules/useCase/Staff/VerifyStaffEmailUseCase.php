<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Staff;

use Domain\Context\Context;

/**
 * スタッフメールアドレス検証ユースケース.
 */
interface VerifyStaffEmailUseCase
{
    /**
     * スタッフのメールアドレスを検証する.
     *
     * @param \Domain\Context\Context $context
     * @param string $token
     * @throws \Lib\Exceptions\NotFoundException
     * @return void
     */
    public function handle(Context $context, string $token): void;
}
