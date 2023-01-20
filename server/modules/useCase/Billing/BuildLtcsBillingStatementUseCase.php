<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Billing\LtcsBillingBundle;
use Domain\Billing\LtcsBillingStatement;
use Domain\Common\Decimal;
use Domain\Context\Context;
use Domain\Office\Office;
use Domain\User\User;
use ScalikePHP\Seq;

/**
 * 介護保険サービス：明細書組み立てユースケース.
 */
interface BuildLtcsBillingStatementUseCase
{
    /**
     * 介護保険サービス：明細書を組み立てる.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Billing\LtcsBillingBundle $bundle
     * @param \Domain\User\User $user
     * @param \Domain\Office\Office $office
     * @param \Domain\Billing\LtcsBillingServiceDetail[]|\ScalikePHP\Seq $details
     * @param \Domain\Common\Decimal $unitCost 単位数単価
     * @param Seq $reports
     * @return \Domain\Billing\LtcsBillingStatement
     */
    public function handle(
        Context $context,
        LtcsBillingBundle $bundle,
        User $user,
        Office $office,
        Seq $details,
        Decimal $unitCost,
        Seq $reports
    ): LtcsBillingStatement;
}
