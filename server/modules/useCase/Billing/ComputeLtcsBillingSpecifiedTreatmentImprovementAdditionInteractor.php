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
 * {@link \UseCase\Billing\ComputeLtcsBillingSpecifiedTreatmentImprovementAdditionUseCase} の実装
 */
class ComputeLtcsBillingSpecifiedTreatmentImprovementAdditionInteractor implements ComputeLtcsBillingSpecifiedTreatmentImprovementAdditionUseCase
{
    use ComputeLtcsBillingServiceDetailSupport;

    /** {@inheritdoc} */
    public function handle(Context $context, LtcsProvisionReport $report, Seq $dictionaryEntries, int $wholeScore, int $scoreWithinMaxBenefitQuota = 0, int $scoreWithinMaxBenefit = 0): Option
    {
        return $this->generateForCategoryOption(
            $report,
            $dictionaryEntries,
            $report->providedIn->endOfMonth(),
            LtcsServiceCodeCategory::fromSpecifiedTreatmentImprovementAddition($report->specifiedTreatmentImprovementAddition),
            $wholeScore,
            $scoreWithinMaxBenefitQuota,
            $scoreWithinMaxBenefit,
        );
    }
}
