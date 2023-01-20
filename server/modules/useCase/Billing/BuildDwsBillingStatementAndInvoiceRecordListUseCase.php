<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Billing\DwsBilling;
use Domain\Context\Context;
use ScalikePHP\Seq;

/**
 * 障害福祉サービス：介護給付費・訓練等給付費等明細書レコード組み立てユースケース.
 */
interface BuildDwsBillingStatementAndInvoiceRecordListUseCase
{
    /**
     * 障害福祉サービス：介護給付費・訓練等給付費等明細書レコードの一覧を組み立てる.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Billing\DwsBilling $billing
     * @param \Domain\Billing\DwsBillingBundle[]|\ScalikePHP\Seq $bundles
     * @return array|\Domain\Exchange\ExchangeRecord[]
     */
    public function handle(Context $context, DwsBilling $billing, Seq $bundles): array;
}
