<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\User;

use Domain\User\UserDwsCalcSpec as DomainUserDwsCalcSpec;
use Domain\User\UserDwsCalcSpecRepository;
use Infrastructure\Repository\EloquentRepository;
use ScalikePHP\Seq;

/**
 * UserDwsCalcSpec Repository Eloquent Implementation.
 */
class UserDwsCalcSpecRepositoryEloquentImpl extends EloquentRepository implements UserDwsCalcSpecRepository
{
    /** {@inheritdoc} */
    protected function lookupHandler(int ...$ids): Seq
    {
        $xs = UserDwsCalcSpec::findMany($ids);
        return Seq::fromArray($xs)->map(fn (UserDwsCalcSpec $x): DomainUserDwsCalcSpec => $x->toDomain());
    }

    /** {@inheritdoc} */
    protected function storeInTransaction(mixed $entity): DomainUserDwsCalcSpec
    {
        assert($entity instanceof DomainUserDwsCalcSpec);
        $x = UserDwsCalcSpec::fromDomain($entity);
        $attr = UserDwsCalcSpecAttr::fromDomain($entity);
        $x->saveIfNotExists();
        $x->attr()->save($attr);
        return $x->refresh()->toDomain();
    }

    /** {@inheritdoc} */
    protected function removeByIdInTransaction(int ...$ids): void
    {
        UserDwsCalcSpecAttr::whereIn('user_dws_calc_spec_id', $ids)->delete();
        UserDwsCalcSpec::destroy($ids);
    }
}
