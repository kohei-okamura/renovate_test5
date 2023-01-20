<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Role;

use Domain\Role\Role as DomainRole;
use Domain\Role\RoleRepository;
use Infrastructure\Repository\EloquentRepository;
use ScalikePHP\Seq;

/**
 * RoleRepository eloquent implementation.
 */
final class RoleRepositoryEloquentImpl extends EloquentRepository implements RoleRepository
{
    /** {@inheritdoc} */
    public function lookupHandler(int ...$ids): Seq
    {
        $x = Role::findMany($ids);
        return Seq::fromArray($x)->map(fn (Role $x) => $x->toDomain());
    }

    /** {@inheritdoc} */
    protected function storeInTransaction(mixed $entity): DomainRole
    {
        assert($entity instanceof DomainRole);
        $role = Role::fromDomain($entity);
        $role->save();
        $role->syncPermissions($entity->permissions);
        return $role->toDomain();
    }

    /** {@inheritdoc} */
    protected function removeByIdInTransaction(int ...$ids): void
    {
        foreach ($ids as $id) {
            $role = Role::find($id);
            $role->permissions()->delete();
        }
        Role::destroy($ids);
    }
}
