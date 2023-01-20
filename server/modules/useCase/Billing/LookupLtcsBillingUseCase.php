<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Context\Context;
use Domain\Permission\Permission;
use ScalikePHP\Seq;

/**
 * 介護保険サービス：請求取得ユースケース.
 */
interface LookupLtcsBillingUseCase
{
    /**
     * 介護保険サービス：請求を取得する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Permission\Permission $permission
     * @param int ...$ids
     * @return \Domain\Billing\LtcsBilling[]|\ScalikePHP\Seq
     */
    public function handle(Context $context, Permission $permission, int ...$ids): Seq;
}
