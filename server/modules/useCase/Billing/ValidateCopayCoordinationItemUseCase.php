<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Billing\CopayCoordinationResult;
use Domain\Context\Context;
use Domain\Permission\Permission;
use ScalikePHP\Seq;

/**
 * 利用者負担上限額管理結果票：明細検証ユースケース.
 */
interface ValidateCopayCoordinationItemUseCase
{
    /**
     * 障害福祉サービス利用者負担上限額管理結果票：明細検証 実装.
     *
     * @param \Domain\Context\Context $context
     * @param \ScalikePHP\Seq $items
     * @param \Domain\Billing\CopayCoordinationResult $result
     * @param int $userId
     * @param int $dwsBillingId
     * @param int $dwsBillingBundleId
     * @param \Domain\Permission\Permission $permission
     * @return bool
     */
    public function handle(
        Context $context,
        Seq $items,
        CopayCoordinationResult $result,
        int $userId,
        int $dwsBillingId,
        int $dwsBillingBundleId,
        Permission $permission
    ): bool;
}
