<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Context\Context;
use Domain\ProvisionReport\DwsProvisionReport;
use ScalikePHP\Option;
use ScalikePHP\Seq;

/**
 * 障害福祉サービス：請求：令和3年9月30日までの上乗せ分のサービス詳細組み立てユースケース（居宅介護用）.
 */
interface ComputeDwsHomeHelpServiceDetailCovid19PandemicSpecialAdditionUseCase
{
    /**
     * 令和3年9月30日までの上乗せ分のサービス詳細を組み立てる.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\ProvisionReport\DwsProvisionReport $provisionReport
     * @param int $baseScore 加算対象の単位数
     * @param \Domain\ServiceCodeDictionary\DwsHomeHelpServiceDictionaryEntry[]|\ScalikePHP\Option $dictionaryEntryOption
     * @return \Domain\Billing\DwsBillingServiceDetail[]|\ScalikePHP\Seq
     */
    public function handle(
        Context $context,
        DwsProvisionReport $provisionReport,
        int $baseScore,
        Option $dictionaryEntryOption
    ): Seq;
}
