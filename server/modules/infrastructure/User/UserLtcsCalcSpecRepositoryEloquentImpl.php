<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\User;

use Domain\User\UserLtcsCalcSpec as DomainUserLtcsCalcSpec;
use Domain\User\UserLtcsCalcSpecRepository;
use Infrastructure\Repository\EloquentRepository;
use ScalikePHP\Seq;

/**
 * UserLtcsCalcSpec Repository Eloquent Implementation.
 */
class UserLtcsCalcSpecRepositoryEloquentImpl extends EloquentRepository implements UserLtcsCalcSpecRepository
{
    /** {@inheritdoc} */
    protected function lookupHandler(int ...$ids): Seq
    {
        $xs = UserLtcsCalcSpec::findMany($ids);
        return Seq::fromArray($xs)->map(fn (UserLtcsCalcSpec $x): DomainUserLtcsCalcSpec => $x->toDomain());
    }

    /** {@inheritdoc} */
    protected function storeInTransaction(mixed $entity): DomainUserLtcsCalcSpec
    {
        assert($entity instanceof DomainUserLtcsCalcSpec);
        $x = UserLtcsCalcSpec::fromDomain($entity);
        $attr = UserLtcsCalcSpecAttr::fromDomain($entity);
        $x->saveIfNotExists();
        $x->attr()->save($attr);
        return $x->refresh()->toDomain();
    }

    /** {@inheritdoc} */
    protected function removeByIdInTransaction(int ...$ids): void
    {
        UserLtcsCalcSpecAttr::whereIn('user_ltcs_calc_spec_id', $ids)->delete();
        UserLtcsCalcSpec::destroy($ids);
    }
}
