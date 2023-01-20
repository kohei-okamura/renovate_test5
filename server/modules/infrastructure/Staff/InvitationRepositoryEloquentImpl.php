<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Staff;

use Domain\Staff\Invitation as DomainInvitation;
use Domain\Staff\InvitationRepository;
use Infrastructure\Repository\EloquentRepository;
use ScalikePHP\Option;
use ScalikePHP\Seq;

/**
 * InvitationRepository eloquent implementation.
 */
final class InvitationRepositoryEloquentImpl extends EloquentRepository implements InvitationRepository
{
    /** {@inheritdoc} */
    public function lookupOptionByToken(string $token): Option
    {
        $x = Invitation::query()->where('token', $token)->first();
        return Option::from($x)->map(fn (Invitation $x) => $x->toDomain());
    }

    /** {@inheritdoc} */
    protected function lookupHandler(int ...$ids): Seq
    {
        $xs = Invitation::findMany($ids);
        return Seq::fromArray($xs)->map(fn (Invitation $x): DomainInvitation => $x->toDomain());
    }

    /** {@inheritdoc} */
    protected function storeInTransaction(mixed $entity): DomainInvitation
    {
        assert($entity instanceof DomainInvitation);
        $invitation = Invitation::fromDomain($entity);
        $invitation->save();
        $invitation->roles()->sync($entity->roleIds);
        $invitation->offices()->sync($entity->officeIds);
        $invitation->officeGroups()->sync($entity->officeGroupIds);
        return $invitation->toDomain();
    }

    /** {@inheritdoc} */
    protected function removeByIdInTransaction(int ...$ids): void
    {
        Invitation::destroy($ids);
    }
}
