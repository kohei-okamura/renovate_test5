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
 * 訪問介護中山間地域等提供加算のサービス詳細を生成するユースケース.
 */
interface ComputeLtcsBillingUserLocationAdditionUseCase
{
    /**
     * 訪問介護中山間地域等提供加算のサービス詳細を生成する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\ProvisionReport\LtcsProvisionReport $report
     * @param \Domain\User\UserLtcsCalcSpec[]&\ScalikePHP\Option $userCalcSpec
     * @param \Domain\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionaryEntry[]&\ScalikePHP\Seq $dictionaryEntries
     * @param int $baseScore
     * @param bool $usePlan
     * @return \Domain\Billing\LtcsBillingServiceDetail[]&\ScalikePHP\Option
     */
    public function handle(Context $context, LtcsProvisionReport $report, Option $userCalcSpec, Seq $dictionaryEntries, int $baseScore, bool $usePlan = false): Option;
}
