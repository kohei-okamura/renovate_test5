<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Billing\LtcsBilling;
use Domain\Common\Carbon;
use Domain\Common\CarbonRange;
use Domain\Context\Context;

/**
 * 介護保険サービス：請求生成ユースケース.
 */
interface CreateLtcsBillingUseCase
{
    /**
     * 介護保険サービス：請求を生成する.
     *
     * @param \Domain\Context\Context $context
     * @param int $officeId
     * @param \Domain\Common\Carbon $transactedIn
     * @param \Domain\Common\CarbonRange $fixedAt
     * @throws \Throwable
     * @return \Domain\Billing\LtcsBilling
     */
    public function handle(Context $context, int $officeId, Carbon $transactedIn, CarbonRange $fixedAt): LtcsBilling;
}
