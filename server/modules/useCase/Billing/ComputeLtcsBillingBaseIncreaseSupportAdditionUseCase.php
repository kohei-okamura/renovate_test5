<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Context\Context;
use Domain\ProvisionReport\LtcsProvisionReport;
use ScalikePHP\Option;
use ScalikePHP\Seq;

/**
 * 介護職員等ベースアップ等支援加算のサービス詳細を生成するユースケース.
 */
interface ComputeLtcsBillingBaseIncreaseSupportAdditionUseCase
{
    /**
     * 介護職員等ベースアップ等支援加算のサービス詳細を生成する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\ProvisionReport\LtcsProvisionReport $report
     * @param \Domain\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionaryEntry[]&\ScalikePHP\Seq $dictionaryEntries
     * @param int $wholeScore 総単位数
     * @param int $scoreWithinMaxBenefitQuota 種類支給限度基準を超える単位数
     * @param int $scoreWithinMaxBenefit 区分支給限度基準を超える単位数
     * @return \Domain\Billing\LtcsBillingServiceDetail[]&\ScalikePHP\Option
     */
    public function handle(Context $context, LtcsProvisionReport $report, Seq $dictionaryEntries, int $wholeScore, int $scoreWithinMaxBenefitQuota, int $scoreWithinMaxBenefit): Option;
}
