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
use Domain\Shift\ServiceOption;
use ScalikePHP\Seq;

/**
 * {@link ComputeLtcsBillingVitalFunctionsImprovementAdditionUseCase} の実装
 */
class ComputeLtcsBillingVitalFunctionsImprovementAdditionInteractor implements ComputeLtcsBillingVitalFunctionsImprovementAdditionUseCase
{
    use ComputeLtcsBillingServiceDetailSupport;

    /** {@inheritdoc} */
    public function handle(Context $context, LtcsProvisionReport $report, Seq $dictionaryEntries): Seq
    {
        return Seq::from(
            ...$this->generateMonthlyAddition(
                $report,
                $dictionaryEntries,
                LtcsServiceCodeCategory::vitalFunctionsImprovementAddition1(),
                ServiceOption::vitalFunctionsImprovement1()
            ),
            ...$this->generateMonthlyAddition(
                $report,
                $dictionaryEntries,
                LtcsServiceCodeCategory::vitalFunctionsImprovementAddition2(),
                ServiceOption::vitalFunctionsImprovement2()
            )
        );
    }
}
