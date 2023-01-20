<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\UserBilling;

use Domain\Context\Context;
use Domain\Permission\Permission;
use ScalikePHP\Seq;

/**
 * 口座振替データ取得ユースケース.
 */
interface LookupWithdrawalTransactionUseCase
{
    /**
     * IDを指定して口座振替データを取得する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Permission\Permission $permission
     * @param int ...$ids
     * @return \Domain\UserBilling\WithdrawalTransaction[]|\ScalikePHP\Seq
     */
    public function handle(Context $context, Permission $permission, int ...$ids): Seq;
}
