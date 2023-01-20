<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Billing\DwsBillingBundle;
use Domain\Billing\DwsBillingStatement;
use Domain\Context\Context;
use Domain\Office\HomeHelpServiceCalcSpec;
use Domain\Office\Office;
use Domain\Office\VisitingCareForPwsdCalcSpec;
use Domain\User\User;
use ScalikePHP\Option;
use ScalikePHP\Seq;

/**
 * 障害福祉サービス：明細書組み立てユースケース.
 */
interface BuildDwsBillingStatementUseCase
{
    /**
     * 障害福祉サービス：明細書を組み立てる.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Office\Office $office
     * @param \Domain\Billing\DwsBillingBundle $bundle
     * @param null|\Domain\Office\HomeHelpServiceCalcSpec $homeHelpServiceCalcSpec
     * @param null|\Domain\Office\VisitingCareForPwsdCalcSpec $visitingCareForPwsdCalcSpec
     * @param \Domain\User\User $user
     * @param \Domain\Billing\DwsBillingServiceDetail[]&\ScalikePHP\Seq $details
     * @param \Domain\Billing\DwsBillingCopayCoordination[]&\ScalikePHP\Option $copayCoordinationOption 上限管理結果票
     * @param \Domain\Billing\DwsBillingStatement[]&\ScalikePHP\Option $baseStatementOption 更新対象の明細
     * @return \Domain\Billing\DwsBillingStatement
     */
    public function handle(
        Context $context,
        Office $office,
        DwsBillingBundle $bundle,
        ?HomeHelpServiceCalcSpec $homeHelpServiceCalcSpec,
        ?VisitingCareForPwsdCalcSpec $visitingCareForPwsdCalcSpec,
        User $user,
        Seq $details,
        Option $copayCoordinationOption,
        Option $baseStatementOption
    ): DwsBillingStatement;
}
