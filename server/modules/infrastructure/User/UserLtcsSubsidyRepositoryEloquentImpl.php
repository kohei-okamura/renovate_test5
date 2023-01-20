<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\User;

use Domain\User\UserLtcsSubsidy as DomainUserLtcsSubsidy;
use Domain\User\UserLtcsSubsidyRepository;
use Infrastructure\Repository\EloquentRepository;
use ScalikePHP\Seq;

/**
 * SubsidyRepository eloquent Implementation
 */
class UserLtcsSubsidyRepositoryEloquentImpl extends EloquentRepository implements UserLtcsSubsidyRepository
{
    /** {@inheritdoc} */
    protected function lookupHandler(int ...$ids): Seq
    {
        $xs = UserLtcsSubsidy::findMany($ids);
        return Seq::fromArray($xs)->map(fn (UserLtcsSubsidy $x): DomainUserLtcsSubsidy => $x->toDomain());
    }

    /** {@inheritdoc} */
    protected function storeInTransaction(mixed $entity): DomainUserLtcsSubsidy
    {
        assert($entity instanceof DomainUserLtcsSubsidy);

        $userLtcsSubsidy = UserLtcsSubsidy::fromDomain($entity)->saveIfNotExists();

        $attr = UserLtcsSubsidyAttr::fromDomain($entity);
        $userLtcsSubsidy->attr()->save($attr);
        return $userLtcsSubsidy->refresh()->toDomain();
    }

    /** {@inheritdoc} */
    protected function removeByIdInTransaction(int ...$ids): void
    {
        UserLtcsSubsidyAttr::whereIn('user_ltcs_subsidy_id', $ids)->delete();
        UserLtcsSubsidy::destroy($ids);
    }
}
