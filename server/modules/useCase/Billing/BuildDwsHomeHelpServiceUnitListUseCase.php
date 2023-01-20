<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Context\Context;
use Domain\DwsCertification\DwsCertification;
use Domain\ProvisionReport\DwsProvisionReport;
use ScalikePHP\Option;
use ScalikePHP\Seq;

/**
 * 障害福祉サービス請求：サービス実績単位（居宅介護）一覧組み立てユースケース.
 */
interface BuildDwsHomeHelpServiceUnitListUseCase
{
    /**
     * 障害福祉サービス請求：サービス実績単位（居宅介護）の一覧を組み立てる.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\DwsCertification\DwsCertification $certification
     * @param \Domain\ProvisionReport\DwsProvisionReport $report
     * @param \Domain\ProvisionReport\DwsProvisionReport[]|\ScalikePHP\Option $previousReport
     * @param bool $forPlan
     * @throws \Throwable
     * @return \Domain\Billing\DwsHomeHelpServiceUnit[]|\ScalikePHP\Seq
     */
    public function handle(
        Context $context,
        DwsCertification $certification,
        DwsProvisionReport $report,
        Option $previousReport,
        bool $forPlan
    ): Seq;
}
