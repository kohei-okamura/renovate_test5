<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Billing\DwsBilling;
use Domain\Common\Carbon;
use Domain\Common\CarbonRange;
use Domain\Context\Context;

/**
 * 障害福祉サービス：請求生成ユースケース.
 */
interface CreateDwsBillingUseCase
{
    /**
     * 障害福祉サービス：請求を生成する.
     *
     * @param \Domain\Context\Context $context
     * @param int $officeId
     * @param \Domain\Common\Carbon $transactedIn
     * @param \Domain\Common\CarbonRange $fixedAt
     * @throws \Throwable
     * @return \Domain\Billing\DwsBilling
     */
    public function handle(Context $context, int $officeId, Carbon $transactedIn, CarbonRange $fixedAt): DwsBilling;
}
