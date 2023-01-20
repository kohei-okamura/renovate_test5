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
 * 初回加算のサービス詳細を生成するユースケース.
 */
interface ComputeLtcsBillingFirstTimeAdditionUseCase
{
    /**
     * 初回加算のサービス詳細を生成する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\ProvisionReport\LtcsProvisionReport $report
     * @param \Domain\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionaryEntry|\ScalikePHP\Seq $dictionaryEntries
     * @param bool $usePlan
     * @return \Domain\Billing\LtcsBillingServiceDetail[]|\ScalikePHP\Option
     */
    public function handle(Context $context, LtcsProvisionReport $report, Seq $dictionaryEntries, bool $usePlan = false): Option;
}
