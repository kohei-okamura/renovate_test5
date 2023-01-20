<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\User;

use Domain\User\UserDwsSubsidy as DomainUserDwsSubsidy;
use Domain\User\UserDwsSubsidyRepository;
use Infrastructure\Repository\EloquentRepository;
use ScalikePHP\Seq;

/**
 * UserDwsSubsidyRepository Eloquent Implementation.
 */
class UserDwsSubsidyRepositoryEloquentImpl extends EloquentRepository implements UserDwsSubsidyRepository
{
    /**
     * {@inheritdoc}
     */
    protected function lookupHandler(int ...$ids): Seq
    {
        $xs = UserDwsSubsidy::findMany($ids);
        return Seq::fromArray($xs)->map(fn (UserDwsSubsidy $x): DomainUserDwsSubsidy => $x->toDomain());
    }

    /**
     * {@inheritdoc}
     */
    protected function storeInTransaction(mixed $entity): DomainUserDwsSubsidy
    {
        assert($entity instanceof DomainUserDwsSubsidy);

        $dwsSubsidy = UserDwsSubsidy::fromDomain($entity)->saveIfNotExists();
        $attr = UserDwsSubsidyAttr::fromDomain($entity);
        $dwsSubsidy->attr()->save($attr);
        return $dwsSubsidy->refresh()->toDomain();
    }

    /**
     * {@inheritdoc}
     */
    protected function removeByIdInTransaction(int ...$ids): void
    {
        UserDwsSubsidyAttr::whereIn('user_dws_subsidy_id', $ids)->delete();
        UserDwsSubsidy::destroy($ids);
    }
}
