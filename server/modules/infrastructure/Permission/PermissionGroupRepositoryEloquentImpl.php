<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Permission;

use Domain\Permission\PermissionGroupRepository;
use Infrastructure\Repository\EloquentRepository;
use Lib\Exceptions\LogicException;
use ScalikePHP\Seq;

/**
 * PermissionGroupRepository eloquent implementation.
 */
final class PermissionGroupRepositoryEloquentImpl extends EloquentRepository implements
    PermissionGroupRepository,
    PermissionGroupRepositoryFallback
{
    /** {@inheritdoc} */
    public function lookupHandler(int ...$ids): Seq
    {
        $xs = PermissionGroup::findMany($ids);
        return Seq::fromArray($xs)->map(fn (PermissionGroup $x) => $x->toDomain());
    }

    /** {@inheritdoc} */
    protected function storeInTransaction(mixed $entity): never
    {
        throw new LogicException('PermissionGroup can not store to repository');
    }

    /** {@inheritdoc} */
    protected function removeByIdInTransaction(int ...$ids): void
    {
        throw new LogicException('PermissionGroup can not remove from repository');
    }
}
