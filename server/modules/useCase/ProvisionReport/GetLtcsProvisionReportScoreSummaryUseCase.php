<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\ProvisionReport;

use Domain\Common\Carbon;
use Domain\Context\Context;
use Domain\Office\HomeVisitLongTermCareSpecifiedOfficeAddition;
use Domain\Office\LtcsBaseIncreaseSupportAddition;
use Domain\Office\LtcsOfficeLocationAddition;
use Domain\Office\LtcsSpecifiedTreatmentImprovementAddition;
use Domain\Office\LtcsTreatmentImprovementAddition;
use Domain\ProvisionReport\LtcsProvisionReportOverScore;
use ScalikePHP\Seq;

/**
 * 介護保険サービス：予実合計単位数取得ユースケース.
 */
interface GetLtcsProvisionReportScoreSummaryUseCase
{
    /**
     * 介護保険サービス：予実の合計単位数取得.
     *
     * @param \Domain\Context\Context $context
     * @param int $officeId
     * @param int $userId
     * @param \Domain\Common\Carbon $providedIn
     * @param \Domain\ProvisionReport\LtcsProvisionReportEntry[]&\ScalikePHP\Seq $entries
     * @param \Domain\Office\HomeVisitLongTermCareSpecifiedOfficeAddition $homeVisitLongTermCareSpecifiedOfficeAddition
     * @param \Domain\Office\LtcsTreatmentImprovementAddition $ltcsTreatmentImprovementAddition
     * @param \Domain\Office\LtcsSpecifiedTreatmentImprovementAddition $ltcsSpecifiedTreatmentImprovementAddition
     * @param \Domain\Office\LtcsBaseIncreaseSupportAddition $baseIncreaseSupportAddition
     * @param \Domain\Office\LtcsOfficeLocationAddition $ltcsOfficeLocationAddition
     * @param LtcsProvisionReportOverScore $plan
     * @param LtcsProvisionReportOverScore $result
     * @return array
     */
    public function handle(
        Context $context,
        int $officeId,
        int $userId,
        Carbon $providedIn,
        Seq $entries,
        HomeVisitLongTermCareSpecifiedOfficeAddition $homeVisitLongTermCareSpecifiedOfficeAddition,
        LtcsTreatmentImprovementAddition $ltcsTreatmentImprovementAddition,
        LtcsSpecifiedTreatmentImprovementAddition $ltcsSpecifiedTreatmentImprovementAddition,
        LtcsBaseIncreaseSupportAddition $baseIncreaseSupportAddition,
        LtcsOfficeLocationAddition $ltcsOfficeLocationAddition,
        LtcsProvisionReportOverScore $plan,
        LtcsProvisionReportOverScore $result,
    ): array;
}
