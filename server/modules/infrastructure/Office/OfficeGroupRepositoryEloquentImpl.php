<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Office;

use Domain\Office\OfficeGroup as DomainOfficeGroup;
use Domain\Office\OfficeGroupRepository;
use Infrastructure\Repository\EloquentRepository;
use ScalikePHP\Seq;

/**
 * OfficeGroupRepository eloquent implementation.
 */
final class OfficeGroupRepositoryEloquentImpl extends EloquentRepository implements OfficeGroupRepository
{
    /** {@inheritdoc} */
    protected function lookupHandler(int ...$ids): Seq
    {
        $xs = OfficeGroup::findMany($ids);
        return Seq::fromArray($xs)->map(fn (OfficeGroup $x) => $x->toDomain());
    }

    /** {@inheritdoc} */
    protected function storeInTransaction(mixed $entity): DomainOfficeGroup
    {
        assert($entity instanceof DomainOfficeGroup);
        $x = OfficeGroup::fromDomain($entity);
        $x->save();
        return $x->toDomain();
    }

    /** {@inheritdoc} */
    protected function removeByIdInTransaction(int ...$ids): void
    {
        OfficeGroup::destroy($ids);
    }
}
