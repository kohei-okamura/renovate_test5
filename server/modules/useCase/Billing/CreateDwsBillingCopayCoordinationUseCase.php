<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Billing\CopayCoordinationResult;
use Domain\Billing\DwsBillingCopayCoordinationExchangeAim;
use Domain\Context\Context;

/**
 * 利用者負担上限額管理結果票登録ユースケース.
 */
interface CreateDwsBillingCopayCoordinationUseCase
{
    /**
     * 利用者負担上限額管理結果票を登録する.
     *
     * @param \Domain\Context\Context $context
     * @param int $billingId
     * @param int $bundleId
     * @param int $userId
     * @param \Domain\Billing\CopayCoordinationResult $result
     * @param array[]|iterable $items 利用者負担上限額管理結果票：明細 の素
     * @param DwsBillingCopayCoordinationExchangeAim $exchangeAim
     * @throws \Throwable
     * @return array
     */
    public function handle(
        Context $context,
        int $billingId,
        int $bundleId,
        int $userId,
        CopayCoordinationResult $result,
        DwsBillingCopayCoordinationExchangeAim $exchangeAim,
        iterable $items
    ): array;
}
