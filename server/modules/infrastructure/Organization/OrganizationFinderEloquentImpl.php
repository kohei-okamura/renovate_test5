<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Organization;

use Domain\Organization\OrganizationFinder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Infrastructure\Finder\EloquentFinder;
use Infrastructure\Finder\EloquentFinderBooleanFilter;

/**
 * {@link \Domain\Organization\OrganizationFinder} Eloquent 実装.
 */
final class OrganizationFinderEloquentImpl extends EloquentFinder implements OrganizationFinder
{
    use EloquentFinderBooleanFilter;

    /** {@inheritdoc} */
    protected function columns(): array
    {
        return ['organization.*'];
    }

    /** {@inheritdoc} */
    protected function getQueryBuilder(): EloquentBuilder
    {
        return Organization::query()
            ->join('organization_to_attr', 'organization_to_attr.organization_id', '=', 'organization.id')
            ->join('organization_attr', 'organization_attr.id', '=', 'organization_to_attr.organization_attr_id');
    }

    /**
     * {@inheritdoc}
     */
    protected function baseTableName(): string
    {
        return Organization::TABLE;
    }

    /** {@inheritdoc} */
    protected function setCondition(Builder $query, string $key, $value): Builder
    {
        switch ($key) {
            case 'isEnabled':
                return $this->setBooleanCondition($query, 'is_enabled', $value);
            default:
                // Organizationは特殊なので基底クラスのメソッドは利用しない
                return $query;
        }
    }
}
