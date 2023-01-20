<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\UserBilling;

use Domain\Common\Carbon;
use Domain\Context\Context;

/**
 * 利用者請求の入金日更新ユースケース.
 */
interface UpdateUserBillingDepositUseCase
{
    /**
     * 利用者請求の入金日を更新する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Common\Carbon $depositedAt
     * @param array $ids 利用者請求ID
     * @return void
     */
    public function handle(Context $context, Carbon $depositedAt, array $ids): void;
}
