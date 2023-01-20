<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Fixtures;

use Infrastructure\UserBilling\UserBilling;
use Infrastructure\UserBilling\UserBillingContact;
use Infrastructure\UserBilling\UserBillingOtherItem;

/**
 * UserBilling fixture.
 *
 * @mixin \Tests\Unit\Examples\ExamplesConsumer
 */
trait UserBillingFixture
{
    /**
     * 利用者請求 登録.
     *
     * @return void
     */
    protected function createUserBillings(): void
    {
        foreach ($this->examples->userBillings as $entity) {
            $userBilling = UserBilling::fromDomain($entity)->saveIfNotExists();
            foreach ($entity->user->contacts as $index => $domainContact) {
                $contact = UserBillingContact::fromDomain(
                    $domainContact,
                    [
                        'user_billing_id' => $userBilling->id,
                        'sort_order' => $index,
                    ]
                );
                $userBilling->userContacts()->save($contact);
            }
            foreach ($entity->otherItems as $index => $domainOtherItem) {
                $otherItem = UserBillingOtherItem::fromDomain(
                    $domainOtherItem,
                    [
                        'user_billing_id' => $userBilling->id,
                        'sort_order' => $index,
                    ],
                );
                $userBilling->otherItems()->save($otherItem);
            }
        }
    }
}
