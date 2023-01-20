<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\User;

use Domain\Common\Contact;
use Domain\User\User as DomainUser;
use Domain\User\UserRepository;
use Infrastructure\Repository\EloquentRepository;
use ScalikePHP\Seq;

/**
 * UserRepository eloquent implementation.
 */
final class UserRepositoryEloquentImpl extends EloquentRepository implements UserRepository
{
    /** {@inheritdoc} */
    protected function lookupHandler(int ...$id): Seq
    {
        $xs = User::findMany($id);
        return Seq::fromArray($xs)->map(fn (User $x) => $x->toDomain());
    }

    /** {@inheritdoc} */
    protected function storeInTransaction(mixed $entity): DomainUser
    {
        assert($entity instanceof DomainUser);
        $user = User::fromDomain($entity)->saveIfNotExists();
        $attr = UserAttr::fromDomain($entity);
        $user->attr()->save($attr);
        $userContacts = array_map(
            fn (Contact $domainContact, int $index): UserContact => UserContact::fromDomain(
                $domainContact,
                [
                    'user_attr_id' => $attr->id,
                    'sort_order' => $index,
                ]
            ),
            $entity->contacts,
            array_keys($entity->contacts)
        );
        $attr->contacts()->saveMany($userContacts);
        return $user->refresh()->toDomain();
    }

    /** {@inheritdoc} */
    protected function removeByIdInTransaction(int ...$ids): void
    {
        UserAttr::whereIn('user_id', $ids)->delete();
        User::destroy($ids);
    }
}
