<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Role;

use Domain\Role\RoleFinder;
use Illuminate\Database\Eloquent\Builder;
use Infrastructure\Finder\EloquentFinder;

/**
 * {@link \Domain\Role\RoleFinder} Eloquent 実装.
 */
final class RoleFinderEloquentImpl extends EloquentFinder implements RoleFinder
{
    /** {@inheritdoc} */
    protected function getQueryBuilder(): Builder
    {
        return Role::query();
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore NOTE: キーワード検索をしないためignoreとする
     */
    protected function baseTableName(): string
    {
        return Role::TABLE;
    }

    /** {@inheritdoc} */
    protected function getOrderByColumnName(string $orderBy): string
    {
        switch ($orderBy) {
            case 'name':
                return 'name';
            case 'sortOrder':
                return 'sort_order';
            default:
                return parent::getOrderByColumnName($orderBy);
        }
    }
}
