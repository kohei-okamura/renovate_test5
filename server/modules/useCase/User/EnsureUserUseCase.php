<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\User;

use Domain\Context\Context;
use Domain\Permission\Permission;

/**
 * 利用者保証ユースケース.
 */
interface EnsureUserUseCase
{
    /**
     * IDを指定して利用者の保証を行う.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Permission\Permission $permission
     * @param int $id ユーザID
     * @throws \Lib\Exceptions\NotFoundException
     * @return void
     */
    public function handle(Context $context, Permission $permission, int $id): void;
}
