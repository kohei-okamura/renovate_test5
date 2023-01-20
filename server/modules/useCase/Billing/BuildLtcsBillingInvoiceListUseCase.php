<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Billing\LtcsBillingBundle;
use Domain\Context\Context;
use ScalikePHP\Seq;

/**
 * 介護保険サービス：請求書一覧組み立てユースケース.
 */
interface BuildLtcsBillingInvoiceListUseCase
{
    /**
     * 介護保険サービス：請求単位を組み立てる.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Billing\LtcsBillingBundle $bundle
     * @param \Domain\Billing\LtcsBillingStatement[]|\ScalikePHP\Seq $statements
     * @return \Domain\Billing\LtcsBillingInvoice[]|\ScalikePHP\Seq
     */
    public function handle(Context $context, LtcsBillingBundle $bundle, Seq $statements): Seq;
}
