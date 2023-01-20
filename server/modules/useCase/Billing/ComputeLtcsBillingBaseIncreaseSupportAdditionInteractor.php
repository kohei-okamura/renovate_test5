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
use ScalikePHP\Option;
use ScalikePHP\Seq;

/**
 * 介護職員等ベースアップ等支援加算のサービス詳細を生成するユースケース実装.
 */
final class ComputeLtcsBillingBaseIncreaseSupportAdditionInteractor implements ComputeLtcsBillingBaseIncreaseSupportAdditionUseCase
{
    use ComputeLtcsBillingServiceDetailSupport;

    /** {@inheritdoc} */
    public function handle(Context $context, LtcsProvisionReport $report, Seq $dictionaryEntries, int $wholeScore, int $scoreWithinMaxBenefitQuota, int $scoreWithinMaxBenefit): Option
    {
        // 2022年10月サービス提供分から算定可能（令和4年10月改定）
        return $report->providedIn->gte('2022-10-01')
            ? $this->generateForCategoryOption(
                $report,
                $dictionaryEntries,
                $report->providedIn->endOfMonth(),
                LtcsServiceCodeCategory::fromBaseIncreaseSupportAddition($report->baseIncreaseSupportAddition),
                $wholeScore,
                $scoreWithinMaxBenefitQuota,
                $scoreWithinMaxBenefit,
            )
            : Option::none();
    }
}
