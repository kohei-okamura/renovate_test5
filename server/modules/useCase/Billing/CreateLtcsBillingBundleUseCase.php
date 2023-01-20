<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Billing\LtcsBilling;
use Domain\Billing\LtcsBillingBundle;
use Domain\Common\Carbon;
use Domain\Context\Context;
use ScalikePHP\Seq;

/**
 * 介護保険サービス：請求単位生成ユースケース.
 */
interface CreateLtcsBillingBundleUseCase
{
    /**
     * 介護保険サービス：請求単位を生成する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Billing\LtcsBilling $billing
     * @param \Domain\Common\Carbon $providedIn
     * @param \Domain\ProvisionReport\LtcsProvisionReport[]|\ScalikePHP\Seq $reports
     * @throws \Throwable
     * @return \Domain\Billing\LtcsBillingBundle
     */
    public function handle(Context $context, LtcsBilling $billing, Carbon $providedIn, Seq $reports): LtcsBillingBundle;
}
