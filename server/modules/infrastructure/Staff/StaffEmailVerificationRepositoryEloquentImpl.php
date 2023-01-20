<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Staff;

use Domain\Staff\StaffEmailVerification as DomainStaffEmailVerification;
use Domain\Staff\StaffEmailVerificationRepository;
use Infrastructure\Repository\EloquentRepository;
use ScalikePHP\Option;
use ScalikePHP\Seq;

/**
 * StaffEmailVerificationRepository eloquent implementation.
 */
final class StaffEmailVerificationRepositoryEloquentImpl extends EloquentRepository implements StaffEmailVerificationRepository
{
    /** {@inheritdoc} */
    public function lookupHandler(int ...$ids): Seq
    {
        $xs = StaffEmailVerification::findMany($ids);
        return Seq::fromArray($xs)->map(fn (StaffEmailVerification $x) => $x->toDomain());
    }

    /** {@inheritdoc} */
    public function lookupOptionByToken(string $token): Option
    {
        $x = StaffEmailVerification::where('token', $token)->first();
        return Option::from($x)->map(
            fn (StaffEmailVerification $staffEmailVerification) => $staffEmailVerification->toDomain()
        );
    }

    /** {@inheritdoc} */
    protected function storeInTransaction(mixed $entity): DomainStaffEmailVerification
    {
        assert($entity instanceof DomainStaffEmailVerification);
        $staffEmailVerification = StaffEmailVerification::fromDomain($entity);
        $staffEmailVerification->save();
        return $staffEmailVerification->refresh()->toDomain();
    }

    /** {@inheritdoc} */
    protected function removeByIdInTransaction(int ...$ids): void
    {
        StaffEmailVerification::destroy($ids);
    }
}
