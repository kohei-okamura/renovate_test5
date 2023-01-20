<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Billing\DwsBillingBundle;
use Domain\Context\Context;
use Domain\ProvisionReport\DwsProvisionReport;
use ScalikePHP\Option;
use ScalikePHP\Seq;

/**
 * 障害福祉サービス：サービス提供実績記録票生成ユースケース.
 */
interface BuildDwsBillingServiceReportListUseCase
{
    /**
     * サービス提供実績記録票を生成する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Billing\DwsBillingBundle $bundle
     * @param \Domain\ProvisionReport\DwsProvisionReport $provisionReport
     * @param \Domain\ProvisionReport\DwsProvisionReport[]&\ScalikePHP\Option $previousProvisionReport
     * @throws \Throwable
     * @return \Domain\Billing\DwsBillingServiceReport[]&\ScalikePHP\Seq
     */
    public function handle(
        Context $context,
        DwsBillingBundle $bundle,
        DwsProvisionReport $provisionReport,
        Option $previousProvisionReport
    ): Seq;
}
