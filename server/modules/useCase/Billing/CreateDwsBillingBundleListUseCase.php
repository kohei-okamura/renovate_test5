<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Billing\DwsBilling;
use Domain\Common\Carbon;
use Domain\Context\Context;
use Domain\Office\Office;
use ScalikePHP\Seq;

/**
 * 障害福祉サービス：請求単位一覧生成ユースケース.
 */
interface CreateDwsBillingBundleListUseCase
{
    /**
     * 障害福祉サービス：請求単位を生成する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Office\Office $office
     * @param \Domain\Billing\DwsBilling $billing
     * @param \Domain\Common\Carbon $providedIn サービス提供年月
     * @param \Domain\Billing\DwsBillingSource[]|\ScalikePHP\Seq $sources
     * @throws \Throwable
     * @return \Domain\Billing\DwsBillingBundle[]|\ScalikePHP\Seq
     */
    public function handle(
        Context $context,
        Office $office,
        DwsBilling $billing,
        Carbon $providedIn,
        Seq $sources
    ): Seq;
}
