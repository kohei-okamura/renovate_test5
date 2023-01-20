<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Organization;

use Domain\Organization\OrganizationSetting as DomainOrganizationSetting;
use Domain\Organization\OrganizationSettingRepository;
use Infrastructure\Repository\EloquentRepository;
use ScalikePHP\Map;
use ScalikePHP\Seq;

/**
 * {@link \Domain\Organization\OrganizationSettingRepository} の実装.
 */
final class OrganizationSettingRepositoryEloquentImpl extends EloquentRepository implements OrganizationSettingRepository
{
    /** {@inheritdoc} */
    public function lookupByOrganizationId(int ...$ids): Map
    {
        $xs = OrganizationSetting::whereIn('organization_id', $ids)->get();
        return Seq::fromArray($xs)
            ->map(fn (OrganizationSetting $x): DomainOrganizationSetting => $x->toDomain())
            ->groupBy(fn (DomainOrganizationSetting $x): int => $x->organizationId);
    }

    /** {@inheritdoc} */
    protected function lookupHandler(int ...$ids): Seq
    {
        $xs = OrganizationSetting::findMany($ids);
        return Seq::fromArray($xs)->map(fn (OrganizationSetting $x): DomainOrganizationSetting => $x->toDomain());
    }

    /** {@inheritdoc} */
    protected function storeInTransaction(mixed $entity): DomainOrganizationSetting
    {
        assert($entity instanceof DomainOrganizationSetting);
        $organizationSetting = OrganizationSetting::fromDomain($entity);
        $organizationSetting->save();
        return $organizationSetting->refresh()->toDomain();
    }

    /** {@inheritdoc} */
    protected function removeByIdInTransaction(int ...$ids): void
    {
        OrganizationSetting::destroy($ids);
    }
}
