<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Billing\DwsBilling;
use Domain\Context\Context;
use ScalikePHP\Option;
use ScalikePHP\Seq;

/**
 * 障害福祉サービス：利用者負担上限額管理結果票 CSV 生成ユースケース.
 */
interface CreateDwsBillingCopayCoordinationCsvUseCase
{
    /**
     * 障害福祉サービス：利用者負担上限額管理結果票 CSV を生成する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Billing\DwsBilling $billing
     * @param \Domain\Billing\DwsBillingBundle[]|\ScalikePHP\Seq $bundles
     * @return \Domain\Billing\DwsBillingFile[]|\ScalikePHP\Option
     */
    public function handle(Context $context, DwsBilling $billing, Seq $bundles): Option;
}
