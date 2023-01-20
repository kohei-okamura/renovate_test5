<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Fixtures;

use Infrastructure\BankAccount\BankAccount;
use Infrastructure\BankAccount\BankAccountAttr;

/**
 * BankAccount fixture.
 *
 * @mixin \Tests\Unit\Examples\ExamplesConsumer
 */
trait BankAccountFixture
{
    /**
     * 銀行口座 登録.
     *
     * @return void
     */
    protected function createBankAccounts(): void
    {
        foreach ($this->examples->bankAccounts as $entity) {
            $bankAccount = BankAccount::fromDomain($entity)->saveIfNotExists();
            $bankAccount->attr()->save(BankAccountAttr::fromDomain($entity));
        }
    }
}
