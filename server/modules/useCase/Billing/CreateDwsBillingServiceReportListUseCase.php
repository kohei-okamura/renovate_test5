<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Billing\DwsBillingBundle;
use Domain\Context\Context;
use ScalikePHP\Seq;

/**
 * 障害福祉サービス：サービス提供実績記録票一覧ユースケース.
 */
interface CreateDwsBillingServiceReportListUseCase
{
    /**
     * サービス提供実績記録票の一覧を生成する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Billing\DwsBillingBundle $bundle
     * @param \Domain\ProvisionReport\DwsProvisionReport[]|Seq $provisionReports
     * @param \Domain\ProvisionReport\DwsProvisionReport[]|Seq $previousProvisionReports
     * @throws \Throwable
     * @return \Domain\Billing\DwsBillingServiceReport[]|Seq
     */
    public function handle(Context $context, DwsBillingBundle $bundle, Seq $provisionReports, Seq $previousProvisionReports): Seq;
}
