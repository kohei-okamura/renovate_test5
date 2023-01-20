<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Billing\DwsBillingBundle;
use Domain\Context\Context;
use ScalikePHP\Seq;

/**
 * 障害福祉サービス：サービス提供実績記録票リフレッシュユースケース.
 */
interface RefreshDwsBillingServiceReportUseCase
{
    /**
     * 障害福祉サービス：サービス提供実績記録票をリフレッシュする.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Billing\DwsBillingBundle $bundle
     * @param \Domain\ProvisionReport\DwsProvisionReport[]&\ScalikePHP\Seq $provisionReports リフレッシュに使用する予実
     * @param \Domain\Billing\DwsBillingServiceReport[]&\ScalikePHP\Seq $serviceReports リフレッシュ対象のサービス提供実績記録票一覧
     * @param \Domain\ProvisionReport\DwsProvisionReport[]&\ScalikePHP\Seq $previousProvisionReports 前月の予実
     * @throws \Throwable
     * @return void
     */
    public function handle(
        Context $context,
        DwsBillingBundle $bundle,
        Seq $provisionReports,
        Seq $serviceReports,
        Seq $previousProvisionReports
    ): void;
}
