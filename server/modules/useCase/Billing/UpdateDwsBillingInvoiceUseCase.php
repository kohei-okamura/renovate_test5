<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Billing\DwsBillingInvoice;
use Domain\Context\Context;

/**
 * 障害福祉サービス：請求書更新ユースケース.
 */
interface UpdateDwsBillingInvoiceUseCase
{
    /**
     * 障害福祉サービス：請求書を更新する.
     *
     * @param \Domain\Context\Context $context
     * @param int $billingId 請求ID
     * @param int $bundleId 請求単位ID
     * @throws \Throwable
     * @return \Domain\Billing\DwsBillingInvoice
     */
    public function handle(Context $context, int $billingId, int $bundleId): DwsBillingInvoice;
}
