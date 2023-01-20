<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Billing\LtcsBilling;
use Domain\Billing\LtcsBillingBundle;
use Domain\Context\Context;

/**
 * 介護保険サービス：介護給付費請求書・明細書レコード組み立てユースケース.
 */
interface BuildLtcsBillingInvoiceRecordListUseCase
{
    /**
     * 介護保険サービス：介護給付費請求書・明細書レコードを組み立てる.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Billing\LtcsBilling $billing
     * @param \Domain\Billing\LtcsBillingBundle $bundle
     * @return array|\Domain\Exchange\ExchangeRecord[]
     */
    public function handle(Context $context, LtcsBilling $billing, LtcsBillingBundle $bundle): array;
}
