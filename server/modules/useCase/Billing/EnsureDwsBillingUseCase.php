<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Context\Context;
use Domain\Permission\Permission;

/**
 * 障害福祉サービス：請求保証ユースケース.
 */
interface EnsureDwsBillingUseCase
{
    /**
     * dwsBillingId を指定して障害福祉サービス：請求の保証を行う.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Permission\Permission $permission
     * @param int $dwsBillingId
     */
    public function handle(Context $context, Permission $permission, int $dwsBillingId): void;
}
