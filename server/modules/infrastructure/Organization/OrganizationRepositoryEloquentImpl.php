<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Organization;

use Domain\Organization\Organization as DomainOrganization;
use Domain\Organization\OrganizationRepository;
use Infrastructure\Repository\EloquentRepository;
use ScalikePHP\Option;
use ScalikePHP\Seq;

/**
 * OrganizationRepository eloquent implementation.
 */
final class OrganizationRepositoryEloquentImpl extends EloquentRepository implements OrganizationRepository, OrganizationRepositoryFallback
{
    /** {@inheritdoc} */
    public function lookupOptionByCode(string $code): Option
    {
        $x = Organization::where('code', '=', $code)->first();
        return Option::from($x)->map(fn (Organization $x) => $x->toDomain());
    }

    /** {@inheritdoc} */
    protected function lookupHandler(int ...$ids): Seq
    {
        $xs = Organization::findMany($ids);
        return Seq::fromArray($xs)->map(fn (Organization $x) => $x->toDomain());
    }

    /** {@inheritdoc} */
    protected function storeInTransaction(mixed $entity): DomainOrganization
    {
        assert($entity instanceof DomainOrganization);
        $organization = Organization::fromDomain($entity)->saveIfNotExists();
        $attr = OrganizationAttr::fromDomain($entity);
        $organization->attr()->save($attr);
        return $organization->refresh()->toDomain();
    }

    /** {@inheritdoc} */
    protected function removeByIdInTransaction(int ...$ids): void
    {
        OrganizationAttr::whereIn('organization_id', $ids)->delete();
        Organization::destroy($ids);
    }
}
