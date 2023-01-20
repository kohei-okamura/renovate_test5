<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Context\Context;
use Domain\Permission\Permission;
use ScalikePHP\Seq;

/**
 * サービス提供実績記録票取得ユースケース.
 */
interface LookupDwsBillingServiceReportUseCase
{
    /**
     * @param \Domain\Context\Context $context
     * @param \Domain\Permission\Permission $permission
     * @param int $billingId
     * @param int $bundleId
     * @param int ...$ids
     * @return \ScalikePHP\Seq
     */
    public function handle(Context $context, Permission $permission, int $billingId, int $bundleId, int ...$ids): Seq;
}
