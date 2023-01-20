<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Permission;

use Domain\Permission\PermissionGroupFinder;
use Illuminate\Database\Eloquent\Builder;
use Infrastructure\Finder\EloquentFinder;

/**
 * {@link \Domain\Permission\PermissionGroupFinder} Eloquent 実装.
 */
final class PermissionGroupFinderEloquentImpl extends EloquentFinder implements PermissionGroupFinder
{
    /** {@inheritdoc} */
    protected function getQueryBuilder(): Builder
    {
        return PermissionGroup::query();
    }

    /** {@inheritdoc} */
    protected function getOrderByColumnName(string $orderBy): string
    {
        switch ($orderBy) {
            case 'sortOrder':
                return 'sort_order';
            default:
                return parent::getOrderByColumnName($orderBy);
        }
    }
}
