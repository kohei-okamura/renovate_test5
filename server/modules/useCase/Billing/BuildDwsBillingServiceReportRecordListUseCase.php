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
 * サービス提供実績記録票レコード組み立てユースケース.
 */
interface BuildDwsBillingServiceReportRecordListUseCase
{
    /**
     * 障害福祉サービス:サービス提供実績記録票レコード組み立て
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Billing\DwsBilling $billing
     * @param \Domain\Billing\DwsBillingBundle[]|\ScalikePHP\Seq $bundles
     * @return array
     */
    public function handle(Context $context, DwsBilling $billing, Seq $bundles): array;
}
