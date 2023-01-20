<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 *  UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\BankAccount;

use Domain\BankAccount\BankAccount;
use Domain\Context\Context;

/**
 *スタッフ銀行口座編集ユースケース。
 */
interface EditStaffBankAccountUseCase
{
    /**
     * スタッフの銀行口座を編集する。
     *
     * @param \Domain\Context\Context $context
     * @param int $staffId
     * @param array $values
     * @return \Domain\BankAccount\BankAccount
     */
    public function handle(Context $context, int $staffId, array $values): BankAccount;
}
