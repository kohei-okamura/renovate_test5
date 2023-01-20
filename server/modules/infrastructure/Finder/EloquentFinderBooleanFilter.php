<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Finder;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;

/**
 * {@link \Infrastructure\Finder\EloquentFinder} に真偽値による検索機能を追加するためのユーティリティ.
 */
trait EloquentFinderBooleanFilter
{
    /**
     * 検索条件：指定したカラムが指定した値（列挙型）である.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $column
     * @param bool $value
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function setBooleanCondition(EloquentBuilder $query, string $column, bool $value): EloquentBuilder
    {
        return $query->where($column, '=', $value);
    }
}
