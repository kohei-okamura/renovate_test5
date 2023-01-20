<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\UserBilling;

use Domain\Context\Context;

/**
 * 利用者請求入金日削除ユースケース.
 */
interface DeleteUserBillingDepositUseCase
{
    /**
     * 利用者請求入金日を削除する.
     *
     * @param \Domain\Context\Context $context
     * @param int[] $ids
     */
    public function handle(Context $context, int ...$ids): void;
}
