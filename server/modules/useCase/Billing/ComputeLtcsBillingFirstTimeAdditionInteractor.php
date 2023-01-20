<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Context\Context;
use Domain\ProvisionReport\LtcsProvisionReport;
use Domain\ProvisionReport\LtcsProvisionReportEntry;
use Domain\ServiceCodeDictionary\LtcsServiceCodeCategory;
use Domain\Shift\ServiceOption;
use ScalikePHP\Option;
use ScalikePHP\Seq;

/**
 * {@link \UseCase\Billing\ComputeLtcsBillingFirstTimeAdditionUseCase} の実装.
 */
final class ComputeLtcsBillingFirstTimeAdditionInteractor implements ComputeLtcsBillingFirstTimeAdditionUseCase
{
    use ComputeLtcsBillingServiceDetailSupport;

    /** {@inheritdoc} */
    public function handle(Context $context, LtcsProvisionReport $report, Seq $dictionaryEntries, bool $usePlan = false): Option
    {
        $xs = Seq::from(...$report->entries)
            ->filter(fn (LtcsProvisionReportEntry $x): bool => $x->hasOption(ServiceOption::firstTime()))
            ->flatMap(fn (LtcsProvisionReportEntry $x): iterable => $usePlan ? $x->plans : $x->results);

        return $xs->isEmpty()
            ? Option::none()
            : Option::from($this->generateForCategory(
                $report,
                $dictionaryEntries,
                $xs->min(),
                LtcsServiceCodeCategory::firstTimeAddition()
            ));
    }
}
