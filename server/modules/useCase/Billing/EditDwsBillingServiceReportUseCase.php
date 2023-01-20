<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Billing\DwsBillingServiceReport;
use Domain\Context\Context;

/**
 * サービス実績記録票編集ユースケース.
 */
interface EditDwsBillingServiceReportUseCase
{
    /**
     * 障害福祉サービス：サービス実績記録票を編集する.
     *
     * @param \Domain\Context\Context $context
     * @param int $dwsBillingId
     * @param int $dwsBillingBundleId
     * @param int $id
     * @param array $values
     * @return \Domain\Billing\DwsBillingServiceReport
     */
    public function handle(
        Context $context,
        int $dwsBillingId,
        int $dwsBillingBundleId,
        int $id,
        array $values
    ): DwsBillingServiceReport;
}
