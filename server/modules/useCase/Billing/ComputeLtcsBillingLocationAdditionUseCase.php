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
 * 地域加算のサービス詳細を生成するユースケース.
 */
interface ComputeLtcsBillingLocationAdditionUseCase
{
    /**
     * 地域加算（※）のサービス詳細を生成する.
     *
     * ※地域加算＝下記のいずれか.
     *
     * - 特別地域訪問介護加算
     * - 小規模事業所加算（中山間地域等における小規模事業所加算）
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\ProvisionReport\LtcsProvisionReport $report
     * @param \Domain\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionaryEntry&\ScalikePHP\Seq $dictionaryEntries
     * @param int $baseScore
     * @param bool $usePlan
     * @return \Domain\Billing\LtcsBillingServiceDetail[]&\ScalikePHP\Option
     */
    public function handle(Context $context, LtcsProvisionReport $report, Seq $dictionaryEntries, int $baseScore, bool $usePlan = false): Option;
}
