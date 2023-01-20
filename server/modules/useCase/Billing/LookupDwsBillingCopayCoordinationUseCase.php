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
 * 利用者負担上限管理結果票取得ユースケース.
 */
interface LookupDwsBillingCopayCoordinationUseCase
{
    /**
     * @param \Domain\Context\Context $context
     * @param \Domain\Permission\Permission $permission
     * @param int $dwsBillingId
     * @param int $dwsBillingBundleId
     * @param int ...$ids
     * @return \Domain\Billing\DwsBillingCopayCoordination[]|\ScalikePHP\Seq
     */
    public function handle(
        Context $context,
        Permission $permission,
        int $dwsBillingId,
        int $dwsBillingBundleId,
        int ...$ids
    ): Seq;
}
