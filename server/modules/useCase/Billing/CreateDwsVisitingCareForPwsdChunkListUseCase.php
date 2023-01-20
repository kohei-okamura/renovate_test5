<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Context\Context;
use Domain\DwsCertification\DwsCertification;
use Domain\ProvisionReport\DwsProvisionReport;
use ScalikePHP\Seq;

/**
 * 障害福祉サービス：サービス単位（重度訪問介護）一覧生成ユースケース.
 */
interface CreateDwsVisitingCareForPwsdChunkListUseCase
{
    /**
     * 障害福祉サービス請求：サービス単位（重度訪問介護）の一覧を生成する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\DwsCertification\DwsCertification $certification
     * @param \Domain\ProvisionReport\DwsProvisionReport $provisionReport
     * @param bool $isPlan
     * @throws \Throwable
     * @return \Domain\Billing\DwsVisitingCareForPwsdChunk[]|\ScalikePHP\Seq
     */
    public function handle(
        Context $context,
        DwsCertification $certification,
        DwsProvisionReport $provisionReport,
        bool $isPlan = false
    ): Seq;
}
