<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Context\Context;
use Domain\ProvisionReport\LtcsProvisionReport;
use ScalikePHP\Option;
use ScalikePHP\Seq;

/**
 * 介護職員等特定処遇改善加算のサービス詳細を生成するユースケース.
 */
interface ComputeLtcsBillingSpecifiedTreatmentImprovementAdditionUseCase
{
    /**
     * 介護職員等特定処遇改善加算のサービス詳細を生成する.
     * //TODO: 利用者請求側の対応まで支給限度基準を超える単位数をデフォルト0にしておく
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\ProvisionReport\LtcsProvisionReport $report
     * @param \ScalikePHP\Seq $dictionaryEntries
     * @param int $wholeScore 総単位数
     * @param int $scoreWithinMaxBenefitQuota 種類支給限度基準を超える単位数
     * @param int $scoreWithinMaxBenefit 区分支給限度基準を超える単位数
     * @return \ScalikePHP\Option
     */
    public function handle(Context $context, LtcsProvisionReport $report, Seq $dictionaryEntries, int $wholeScore, int $scoreWithinMaxBenefitQuota = 0, int $scoreWithinMaxBenefit = 0): Option;
}
