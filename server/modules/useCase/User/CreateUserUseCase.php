<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\User;

use Domain\Context\Context;
use Domain\User\User;

/**
 * 利用者登録ユースケース.
 */
interface CreateUserUseCase
{
    /**
     * 利用者を登録する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\User\User $user
     * @param callable $f
     * @return void
     */
    public function handle(Context $context, User $user, callable $f): void;
}
