<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\UserBilling;

use Domain\Context\Context;
use Domain\Permission\Permission;
use ScalikePHP\Seq;

/**
 * 利用者請求取得ユースケース.
 */
interface LookupUserBillingUseCase
{
    /**
     * IDを指定して利用者情報を取得する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Permission\Permission $permission
     * @param int ...$ids
     * @return \Domain\UserBilling\UserBilling[]|\ScalikePHP\Seq
     */
    public function handle(Context $context, Permission $permission, int ...$ids): Seq;
}
