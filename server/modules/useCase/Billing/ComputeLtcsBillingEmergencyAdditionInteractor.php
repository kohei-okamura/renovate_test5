<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Billing\LtcsBillingServiceDetail;
use Domain\Common\Carbon;
use Domain\Context\Context;
use Domain\ProvisionReport\LtcsProvisionReport;
use Domain\ProvisionReport\LtcsProvisionReportEntry;
use Domain\ServiceCodeDictionary\LtcsServiceCodeCategory;
use Domain\Shift\ServiceOption;
use ScalikePHP\Seq;

/**
 * 緊急時訪問介護加算のサービス詳細を生成するユースケース実装.
 */
final class ComputeLtcsBillingEmergencyAdditionInteractor implements ComputeLtcsBillingEmergencyAdditionUseCase
{
    use ComputeLtcsBillingServiceDetailSupport;

    /** {@inheritdoc} */
    public function handle(
        Context $context,
        LtcsProvisionReport $report,
        Seq $dictionaryEntries,
        bool $usePlan = false
    ): Seq {
        return Seq::from(...$report->entries)
            ->filter(fn (LtcsProvisionReportEntry $x): bool => $x->hasOption(ServiceOption::emergency()))
            ->flatMap(fn (LtcsProvisionReportEntry $x): iterable => $usePlan ? $x->plans : $x->results)
            ->map(fn (Carbon $providedOn): LtcsBillingServiceDetail => $this->generateForCategory(
                $report,
                $dictionaryEntries,
                $providedOn,
                LtcsServiceCodeCategory::emergencyAddition()
            ))
            ->computed();
    }
}
