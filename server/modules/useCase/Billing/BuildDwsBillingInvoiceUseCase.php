<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Billing\DwsBillingBundle;
use Domain\Billing\DwsBillingInvoice;
use Domain\Context\Context;
use ScalikePHP\Seq;

/**
 * 障害福祉サービス：請求書組み立てユースケース.
 */
interface BuildDwsBillingInvoiceUseCase
{
    /**
     * 障害福祉サービス：請求書を組み立てる.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Billing\DwsBillingBundle $bundle
     * @param \Domain\Billing\DwsBillingStatement[]|\ScalikePHP\Seq $statements
     * @throws \Throwable
     * @return \Domain\Billing\DwsBillingInvoice
     */
    public function handle(Context $context, DwsBillingBundle $bundle, Seq $statements): DwsBillingInvoice;
}
