<?php
/**
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\User;

use Domain\Context\Context;
use Domain\Permission\Permission;
use ScalikePHP\Seq;

/**
 * 介護保険サービス：利用者別算定情報取得ユースケース.
 */
interface LookupUserLtcsCalcSpecUseCase
{
    /**
     * IDを指定して介護保険サービス：利用者別算定情報を取得する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Permission\Permission $permission
     * @param int $userId
     * @param int ...$ids
     * @return \Domain\User\UserLtcsCalcSpec[]&\ScalikePHP\Seq
     */
    public function handle(Context $context, Permission $permission, int $userId, int ...$ids): Seq;
}
