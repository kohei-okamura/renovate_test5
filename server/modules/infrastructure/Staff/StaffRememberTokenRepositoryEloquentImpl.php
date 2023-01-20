<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Staff;

use Domain\Staff\StaffRememberToken as DomainStaffRememberToken;
use Domain\Staff\StaffRememberTokenRepository;
use Infrastructure\Repository\EloquentRepository;
use ScalikePHP\Option;
use ScalikePHP\Seq;

/**
 * StaffRememberTokenRepository eloquent implementation.
 */
final class StaffRememberTokenRepositoryEloquentImpl extends EloquentRepository implements StaffRememberTokenRepository
{
    /** {@inheritdoc} */
    public function lookupHandler(int ...$ids): Seq
    {
        $xs = StaffRememberToken::findMany($ids);
        return Seq::fromArray($xs)->map(fn (StaffRememberToken $x) => $x->toDomain());
    }

    /** {@inheritdoc} */
    public function lookupOptionByToken(string $token): Option
    {
        $x = StaffRememberToken::query()->where('token', $token)->first();
        return Option::from($x)->map(fn (StaffRememberToken $staffRememberToken) => $staffRememberToken->toDomain());
    }

    /** {@inheritdoc} */
    protected function storeInTransaction(mixed $entity): DomainStaffRememberToken
    {
        assert($entity instanceof DomainStaffRememberToken);
        $x = StaffRememberToken::fromDomain($entity);
        $x->save();
        return $x->toDomain();
    }

    /** {@inheritdoc} */
    protected function removeByIdInTransaction(int ...$ids): void
    {
        StaffRememberToken::destroy($ids);
    }
}
