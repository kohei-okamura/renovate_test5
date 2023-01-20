<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
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
 * 障害福祉サービス請求：サービス単位（居宅介護）生成ユースケース.
 */
interface CreateDwsHomeHelpServiceChunkListUseCase
{
    /**
     * 障害福祉サービス請求：サービス単位（居宅介護）の一覧を生成する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\DwsCertification\DwsCertification $certification
     * @param \Domain\ProvisionReport\DwsProvisionReport $report
     * @param \Domain\ProvisionReport\DwsProvisionReport[]|option $previousReport
     * @param bool $isPlan
     * @throws \Throwable
     * @return \Domain\Billing\DwsHomeHelpServiceChunk[]|\ScalikePHP\Seq
     */
    public function handle(
        Context $context,
        DwsCertification $certification,
        DwsProvisionReport $report,
        Option $previousReport,
        bool $isPlan = false
    ): Seq;
}
