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
 * 障害福祉サービス：明細書取得ユースケース.
 */
interface LookupDwsBillingStatementUseCase
{
    /**
     * ID を指定して障害福祉サービス：明細書を取得する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Permission\Permission $permission
     * @param int $dwsBillingId 請求ID
     * @param int $dwsBillingBundleId 請求単位 ID
     * @param int ...$ids
     * @return \Domain\Billing\DwsBillingStatement[]|\ScalikePHP\Seq
     */
    public function handle(
        Context $context,
        Permission $permission,
        int $dwsBillingId,
        int $dwsBillingBundleId,
        int ...$ids
    ): iterable;
}
