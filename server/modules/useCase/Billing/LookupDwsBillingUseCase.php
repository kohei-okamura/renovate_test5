<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Context\Context;
use Domain\Permission\Permission;
use ScalikePHP\Seq;

/**
 * 障害福祉サービス：請求取得ユースケース.
 */
interface LookupDwsBillingUseCase
{
    /**
     * ID を指定して障害福祉サービス：請求を取得する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Permission\Permission $permission
     * @param int[] $ids
     * @return \ScalikePHP\Seq
     */
    public function handle(Context $context, Permission $permission, int ...$ids): Seq;
}
