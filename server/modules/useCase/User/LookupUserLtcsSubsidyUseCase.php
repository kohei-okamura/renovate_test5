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
 * 公費情報取得ユースケース.
 */
interface LookupUserLtcsSubsidyUseCase
{
    /**
     * IDを指定して公費情報を取得する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Permission\Permission $permission
     * @param int $userId
     * @param int ...$ids
     * @return \Domain\User\UserLtcsSubsidy[]|\ScalikePHP\Seq
     */
    public function handle(Context $context, Permission $permission, int $userId, int ...$ids): Seq;
}
