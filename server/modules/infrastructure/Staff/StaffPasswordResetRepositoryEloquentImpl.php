<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Staff;

use Domain\Staff\StaffPasswordReset as DomainStaffPasswordReset;
use Domain\Staff\StaffPasswordResetRepository;
use Infrastructure\Repository\EloquentRepository;
use ScalikePHP\Option;
use ScalikePHP\Seq;

/**
 * StaffPasswordResetRepository eloquent implementation.
 */
final class StaffPasswordResetRepositoryEloquentImpl extends EloquentRepository implements StaffPasswordResetRepository
{
    /** {@inheritdoc} */
    public function lookupHandler(int ...$ids): Seq
    {
        $xs = StaffPasswordReset::findMany($ids);
        return Seq::fromArray($xs)->map(fn (StaffPasswordReset $x) => $x->toDomain());
    }

    /** {@inheritdoc} */
    public function lookupOptionByToken(string $token): Option
    {
        $x = StaffPasswordReset::query()->where('token', $token)->first();
        return Option::from($x)->map(fn (StaffPasswordReset $staffPasswordReset) => $staffPasswordReset->toDomain());
    }

    /** {@inheritdoc} */
    protected function storeInTransaction(mixed $entity): DomainStaffPasswordReset
    {
        assert($entity instanceof DomainStaffPasswordReset);
        $x = StaffPasswordReset::fromDomain($entity);
        $x->save();
        return $x->refresh()->toDomain();
    }

    /** {@inheritdoc} */
    protected function removeByIdInTransaction(int ...$ids): void
    {
        StaffPasswordReset::destroy($ids);
    }
}
