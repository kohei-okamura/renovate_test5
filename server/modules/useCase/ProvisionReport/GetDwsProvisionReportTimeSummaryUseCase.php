<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\ProvisionReport;

use Domain\Common\Carbon;
use Domain\Context\Context;
use ScalikePHP\Seq;

/**
 * 障害福祉サービス：予実合計時間数取得ユースケース.
 */
interface GetDwsProvisionReportTimeSummaryUseCase
{
    /**
     * 障害福祉サービス：予実の合計時間数取得.
     *
     * @param \Domain\Context\Context $context
     * @param int $officeId
     * @param int $userId
     * @param \Domain\Common\Carbon $providedIn
     * @param \Domain\ProvisionReport\DwsProvisionReportItem[]|\ScalikePHP\Seq $plans
     * @param \Domain\ProvisionReport\DwsProvisionReportItem[]|\ScalikePHP\Seq $results
     * @throws \Throwable
     * @return array
     */
    public function handle(
        Context $context,
        int $officeId,
        int $userId,
        Carbon $providedIn,
        Seq $plans,
        Seq $results
    ): array;
}
