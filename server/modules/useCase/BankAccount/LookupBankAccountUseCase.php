<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\BankAccount;

use Domain\Context\Context;
use ScalikePHP\Seq;

/**
 * 銀行口座取得ユースケース.
 */
interface LookupBankAccountUseCase
{
    /**
     * ID を指定して銀行口座情報を取得する.
     *
     * @param \Domain\Context\Context $context
     * @param int ...$ids
     * @return \Domain\BankAccount\BankAccount[]|\ScalikePHP\Seq
     */
    public function handle(Context $context, int ...$ids): Seq;
}
