<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\UserBilling;

use Domain\Context\Context;
use Domain\UserBilling\UserBilling;

/**
 * 利用者請求編集ユースケース.
 */
interface UpdateUserBillingUseCase
{
    /**
     * 利用者請求を編集する.
     *
     * @param \Domain\Context\Context $context
     * @param int $id
     * @param array $values
     * @return \Domain\UserBilling\UserBilling
     */
    public function handle(Context $context, int $id, array $values): UserBilling;
}
