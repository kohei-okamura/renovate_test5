<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Context\Context;

/**
 * 利用者負担上限額管理結果票取得ユースケース.
 */
interface GetDwsBillingCopayCoordinationInfoUseCase
{
    /**
     * 利用者負担上限額管理結果票を取得する.
     *
     * @param \Domain\Context\Context $context
     * @param int $dwsBillingId
     * @param int $dwsBillingBundleId
     * @param int $dwsBillingCopayCoordinationId
     * @return array
     */
    public function handle(
        Context $context,
        int $dwsBillingId,
        int $dwsBillingBundleId,
        int $dwsBillingCopayCoordinationId
    ): array;
}
