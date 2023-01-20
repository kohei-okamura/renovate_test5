<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Context\Context;
use Domain\ProvisionReport\LtcsProvisionReport;
use Domain\ServiceCodeDictionary\LtcsServiceCodeCategory;
use Domain\User\LtcsUserLocationAddition;
use Domain\User\UserLtcsCalcSpec;
use ScalikePHP\Option;
use ScalikePHP\Seq;

/**
 * 訪問介護中山間地域等提供加算のサービス詳細を生成するユースケース実装.
 */
final class ComputeLtcsBillingUserLocationAdditionInteractor implements ComputeLtcsBillingUserLocationAdditionUseCase
{
    use ComputeLtcsBillingServiceDetailSupport;

    /** {@inheritdoc} */
    public function handle(Context $context, LtcsProvisionReport $report, Option $userCalcSpec, Seq $dictionaryEntries, int $baseScore, bool $usePlan = false): Option
    {
        $category = $userCalcSpec->flatMap(function (UserLtcsCalcSpec $x): Option {
            return $x->locationAddition === LtcsUserLocationAddition::mountainousArea()
            ? Option::from(LtcsServiceCodeCategory::mountainousAreaAddition())
            : Option::none();
        });
        return $this->generateForCategoryOption(
            $report,
            $dictionaryEntries,
            $report->providedIn->endOfMonth(),
            $category,
            $baseScore,
            $usePlan ? $report->plan->maxBenefitQuotaExcessScore : $report->result->maxBenefitQuotaExcessScore,
            $usePlan ? $report->plan->maxBenefitExcessScore : $report->result->maxBenefitExcessScore,
        );
    }
}
