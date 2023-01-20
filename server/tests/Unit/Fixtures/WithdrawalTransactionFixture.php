<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Fixtures;

use Infrastructure\UserBilling\WithdrawalTransaction;
use Infrastructure\UserBilling\WithdrawalTransactionItem;

/**
 * WithdrawalTransaction fixture.
 *
 * @mixin \Tests\Unit\Examples\ExamplesConsumer
 */
trait WithdrawalTransactionFixture
{
    /**
     * 口座振替データ 登録.
     *
     * @return void
     */
    protected function createWithdrawalTransactions(): void
    {
        foreach ($this->examples->withdrawalTransactions as $entity) {
            $withdrawalTransaction = WithdrawalTransaction::fromDomain($entity)->saveIfNotExists();
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
        }
    }
}
