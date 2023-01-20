<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\ProvisionReport\LtcsProvisionReport;
use ScalikePHP\Seq;

/**
 * 介護保険サービス：請求：令和3年9月30日までの上乗せ分のサービス詳細組み立てユースケース.
 */
interface ComputeLtcsServiceDetailCovid19PandemicSpecialAdditionUseCase
{
    /**
     * 令和3年9月30日までの上乗せ分のサービス詳細を組み立てる.
     *
     * @param \Domain\ProvisionReport\LtcsProvisionReport $provisionReport
     * @param \Domain\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionaryEntry[]|\ScalikePHP\Seq $dictionaryEntries
     * @param int $mainScore
     * @return \Domain\Billing\LtcsBillingServiceDetail[]|\ScalikePHP\Seq
     */
    public function handle(LtcsProvisionReport $provisionReport, Seq $dictionaryEntries, int $mainScore): Seq;
}
