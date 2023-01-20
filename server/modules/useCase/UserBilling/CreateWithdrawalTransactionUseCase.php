<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\UserBilling;

use Domain\Context\Context;
use Domain\UserBilling\WithdrawalTransaction;

/**
 * 口座振替データ作成ユースケース.
 */
interface CreateWithdrawalTransactionUseCase
{
    /**
     * 口座振替データを作成する.
     *
     * @param \Domain\Context\Context $context
     * @param array|int[] $userBillingIds
     * @return \Domain\UserBilling\WithdrawalTransaction
     */
    public function handle(Context $context, array $userBillingIds): WithdrawalTransaction;
}
