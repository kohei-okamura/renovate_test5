<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Office;

use Domain\Office\OfficeGroupFinder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Infrastructure\Finder\EloquentFinder;

/**
 * {@link \Domain\Office\OfficeGroupFinder} Eloquent 実装.
 */
final class OfficeGroupFinderEloquentImpl extends EloquentFinder implements OfficeGroupFinder
{
    /** {@inheritdoc} */
    protected function getQueryBuilder(): Builder
    {
        return OfficeGroup::query();
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore NOTE: キーワード検索をしないためignoreとする
     */
    protected function baseTableName(): string
    {
        return OfficeGroup::TABLE;
    }

    /** {@inheritdoc} */
    protected function setCondition(EloquentBuilder $query, string $key, $value): EloquentBuilder
    {
        switch ($key) {
            case 'ids':
                return $query->whereIn('id', is_array($value) ? $value : [$value]);
            case 'parentOfficeGroupIds':
                return $query->whereIn('parent_office_group_id', is_array($value) ? $value : [$value]);
            default:
                return parent::setCondition($query, $key, $value);
        }
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
