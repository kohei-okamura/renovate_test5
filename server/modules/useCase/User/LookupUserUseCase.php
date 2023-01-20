<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\User;

use Domain\Context\Context;
use Domain\Permission\Permission;
use ScalikePHP\Seq;

/**
 * 利用者取得ユースケース.
 */
interface LookupUserUseCase
{
    /**
     * ID を指定して利用者情報を取得する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Permission\Permission $permission
     * @param int ...$id
     * @return \Domain\User\User[]|\ScalikePHP\Seq
     */
    public function handle(Context $context, Permission $permission, int ...$id): Seq;
}
