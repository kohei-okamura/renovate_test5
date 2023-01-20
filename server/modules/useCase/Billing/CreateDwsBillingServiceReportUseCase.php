<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Billing\DwsBillingBundle;
use Domain\Billing\DwsBillingServiceReport;
use Domain\Context\Context;

/**
 * サービス提供実績記録票生成ユースケース.
 */
interface CreateDwsBillingServiceReportUseCase
{
    /**
     * 障害福祉サービス請求の「サービス提供実績記録票」を生成する.
     *
     * @param \Domain\Context\Context $context
     * @param int $officeId
     * @param \Domain\Billing\DwsBillingBundle $dwsBillingBundle
     * @return \Domain\Billing\DwsBillingServiceReport
     */
    public function handle(
        Context $context,
        int $officeId,
        DwsBillingBundle $dwsBillingBundle
    ): DwsBillingServiceReport;
}
