<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\BankAccount;

use Domain\BankAccount\BankAccount as DomainBankAccount;
use Domain\BankAccount\BankAccountRepository;
use Infrastructure\Repository\EloquentRepository;
use ScalikePHP\Seq;

/**
 * BankAccountRepositoryEloquent eloquent implementation.
 */
final class BankAccountRepositoryEloquentImpl extends EloquentRepository implements BankAccountRepository
{
    /** {@inheritdoc} */
    protected function lookupHandler(int ...$id): Seq
    {
        $xs = BankAccount::find($id);
        return Seq::fromArray($xs)->map(fn (BankAccount $x) => $x->toDomain());
    }

    /** {@inheritdoc} */
    protected function storeInTransaction(mixed $entity): DomainBankAccount
    {
        assert($entity instanceof DomainBankAccount);
        $bankAccount = BankAccount::fromDomain($entity)->saveIfNotExists();
        $attr = BankAccountAttr::fromDomain($entity);
        $bankAccount->attr()->save($attr);
        return $bankAccount->refresh()->toDomain();
    }

    /** {@inheritdoc} */
    protected function removeByIdInTransaction(int ...$ids): void
    {
        BankAccountAttr::whereIn('bank_account_id', $ids)->delete();
        BankAccount::destroy($ids);
    }
}
