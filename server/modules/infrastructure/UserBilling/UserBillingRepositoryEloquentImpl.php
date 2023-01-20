<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\UserBilling;

use Domain\UserBilling\UserBilling as DomainUserBilling;
use Domain\UserBilling\UserBillingRepository;
use Infrastructure\Repository\EloquentRepository;
use ScalikePHP\Seq;

/**
 * UserRepository eloquent implementation.
 */
final class UserBillingRepositoryEloquentImpl extends EloquentRepository implements UserBillingRepository
{
    /** {@inheritdoc} */
    protected function lookupHandler(int ...$ids): Seq
    {
        $xs = UserBilling::findMany($ids);
        return Seq::fromArray($xs)->map(fn (UserBilling $x): DomainUserBilling => $x->toDomain());
    }

    /** {@inheritdoc} */
    protected function storeInTransaction(mixed $entity): DomainUserBilling
    {
        assert($entity instanceof DomainUserBilling);
        $userBilling = UserBilling::fromDomain($entity);
        if ($userBilling->userContacts()->exists()) {
            $userBilling->userContacts()->delete();
        }
        if ($userBilling->otherItems()->exists()) {
            $userBilling->otherItems()->delete();
        }
        $userBilling->save();
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
        return $userBilling->refresh()->toDomain();
    }

    /** {@inheritdoc} */
    protected function removeByIdInTransaction(int ...$ids): void
    {
        UserBilling::destroy($ids);
    }
}
