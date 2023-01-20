<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Context\Context;
use Domain\ProvisionReport\LtcsProvisionReport;
use Domain\ServiceCodeDictionary\LtcsServiceCodeCategory;
use ScalikePHP\Option;
use ScalikePHP\Seq;

/**
 * 地域加算のサービス詳細を生成するユースケース実装.
 */
final class ComputeLtcsBillingLocationAdditionInteractor implements ComputeLtcsBillingLocationAdditionUseCase
{
    use ComputeLtcsBillingServiceDetailSupport;

    /** {@inheritdoc} */
    public function handle(Context $context, LtcsProvisionReport $report, Seq $dictionaryEntries, int $baseScore, bool $usePlan = false): Option
    {
        return $this->generateForCategoryOption(
            $report,
            $dictionaryEntries,
            $report->providedIn->endOfMonth(),
            LtcsServiceCodeCategory::fromOfficeLocationAddition($report->locationAddition),
            $baseScore,
            $usePlan ? $report->plan->maxBenefitQuotaExcessScore : $report->result->maxBenefitQuotaExcessScore,
            $usePlan ? $report->plan->maxBenefitExcessScore : $report->result->maxBenefitExcessScore,
        );
    }
}
