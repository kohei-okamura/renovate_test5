<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Common\Carbon;
use Domain\Context\Context;
use Domain\Office\HomeHelpServiceCalcSpec;
use Domain\Office\VisitingCareForPwsdCalcSpec;
use ScalikePHP\Seq;

/**
 * 障害福祉サービス：明細書：要素一覧組み立てユースケース.
 */
interface BuildDwsBillingStatementElementListUseCase
{
    /**
     * 障害福祉サービス：明細書：要素の一覧を組み立てる.
     *
     * @param \Domain\Context\Context $context
     * @param null|\Domain\Office\HomeHelpServiceCalcSpec $homeHelpServiceCalcSpec
     * @param null|\Domain\Office\VisitingCareForPwsdCalcSpec $visitingCareForPwsdCalcSpec
     * @param bool $enableCopayCoordinationAddition
     * @param \Domain\Common\Carbon $providedIn
     * @param \Domain\Billing\DwsBillingServiceDetail[]|\ScalikePHP\Seq $details
     * @return \Domain\Billing\DwsBillingStatementElement[]|\ScalikePHP\Seq
     */
    public function handle(
        Context $context,
        ?HomeHelpServiceCalcSpec $homeHelpServiceCalcSpec,
        ?VisitingCareForPwsdCalcSpec $visitingCareForPwsdCalcSpec,
        bool $enableCopayCoordinationAddition,
        Carbon $providedIn,
        Seq $details
    ): Seq;
}
