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
 * {@link \UseCase\Billing\ComputeLtcsBillingTreatmentImprovementAdditionUseCase} の実装.
 */
final class ComputeLtcsBillingTreatmentImprovementAdditionInteractor implements ComputeLtcsBillingTreatmentImprovementAdditionUseCase
{
    use ComputeLtcsBillingServiceDetailSupport;

    /** {@inheritdoc} */
    public function handle(Context $context, LtcsProvisionReport $report, Seq $dictionaryEntries, int $wholeScore, int $scoreWithinMaxBenefitQuota = 0, int $scoreWithinMaxBenefit = 0): Option
    {
        return $this->generateForCategoryOption(
            $report,
            $dictionaryEntries,
            $report->providedIn->endOfMonth(),
            LtcsServiceCodeCategory::fromTreatmentImprovementAddition($report->treatmentImprovementAddition),
            $wholeScore,
            $scoreWithinMaxBenefitQuota,
            $scoreWithinMaxBenefit,
        );
    }
}
