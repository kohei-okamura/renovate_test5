<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Context\Context;
use Domain\Permission\Permission;

/**
 * 介護保険サービス：請求単位保証ユースケース.
 */
interface EnsureLtcsBillingBundleUseCase
{
    /**
     * 介護保険サービス：請求単位の保証を行う.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Permission\Permission $permission
     * @param int $billingId
     * @param int $bundleId
     */
    public function handle(Context $context, Permission $permission, int $billingId, int $bundleId): void;
}
