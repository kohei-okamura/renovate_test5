<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Context\Context;
use Domain\ProvisionReport\LtcsProvisionReport;
use ScalikePHP\Seq;

/**
 * 緊急時訪問介護加算のサービス詳細を生成するユースケース.
 */
interface ComputeLtcsBillingEmergencyAdditionUseCase
{
    /**
     *  緊急時訪問介護加算のサービス詳細を生成する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\ProvisionReport\LtcsProvisionReport $report
     * @param \Domain\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionaryEntry[]|\ScalikePHP\Seq $dictionaryEntries
     * @param bool $usePlan
     * @return \Domain\Billing\LtcsBillingServiceDetail[]|\ScalikePHP\Seq
     */
    public function handle(Context $context, LtcsProvisionReport $report, Seq $dictionaryEntries, bool $usePlan = false): Seq;
}
