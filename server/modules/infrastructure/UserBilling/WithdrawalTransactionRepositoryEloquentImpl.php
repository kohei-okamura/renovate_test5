<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\UserBilling;

use Domain\UserBilling\WithdrawalTransaction as DomainWithdrawalTransaction;
use Domain\UserBilling\WithdrawalTransactionRepository;
use Infrastructure\Repository\EloquentRepository;
use ScalikePHP\Seq;

/**
 * {@link \Domain\UserBilling\WithdrawalTransactionRepository} の実装
 */
final class WithdrawalTransactionRepositoryEloquentImpl extends EloquentRepository implements WithdrawalTransactionRepository
{
    /** {@inheritdoc} */
    protected function lookupHandler(int ...$ids): Seq
    {
        $xs = WithdrawalTransaction::findMany($ids);
        return Seq::fromArray($xs)->map(fn (WithdrawalTransaction $x): DomainWithdrawalTransaction => $x->toDomain());
    }

    /** {@inheritdoc} */
    protected function storeInTransaction(mixed $entity): DomainWithdrawalTransaction
    {
        assert($entity instanceof DomainWithdrawalTransaction);
        $withdrawalTransaction = WithdrawalTransaction::fromDomain($entity);
        if ($withdrawalTransaction->items()->exists()) {
            $withdrawalTransaction->items()->delete();
        }
        $withdrawalTransaction->save();
        foreach ($entity->items as $index => $domainItem) {
            $item = WithdrawalTransactionItem::fromDomain(
                $domainItem,
                [
                    'withdrawal_transaction_id' => $withdrawalTransaction->id,
                    'sort_order' => $index,
                ],
            );
            $withdrawalTransaction->items()->save($item);
            $item->userBillings()->sync($domainItem->userBillingIds);
        }
        return $withdrawalTransaction->refresh()->toDomain();
    }

    /** {@inheritdoc} */
    protected function removeByIdInTransaction(int ...$ids): void
    {
        WithdrawalTransaction::destroy($ids);
    }
}
