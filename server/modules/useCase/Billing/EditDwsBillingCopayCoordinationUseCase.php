<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Billing\CopayCoordinationResult;
use Domain\Billing\DwsBillingCopayCoordinationExchangeAim;
use Domain\Context\Context;

/**
 * 利用者負担上限管理結果票 更新ユースケース.
 */
interface EditDwsBillingCopayCoordinationUseCase
{
    /**
     * 利用者負担上限管理結果票を更新する.
     *
     * @param \Domain\Context\Context $context
     * @param int $dwsBillingId
     * @param int $dwsBillingBundleId
     * @param int $copayCoordinationId
     * @param int $userId
     * @param \Domain\Billing\CopayCoordinationResult $result
     * @param \Domain\Billing\DwsBillingCopayCoordinationExchangeAim $exchangeAim
     * @param iterable $items
     * @return array Response JSON用array
     */
    public function handle(
        Context $context,
        int $dwsBillingId,
        int $dwsBillingBundleId,
        int $copayCoordinationId,
        int $userId,
        CopayCoordinationResult $result,
        DwsBillingCopayCoordinationExchangeAim $exchangeAim,
        iterable $items
    ): array;
}
