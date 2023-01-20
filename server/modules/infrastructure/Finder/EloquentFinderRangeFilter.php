<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Finder;

use Domain\Common\Carbon;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;

/**
 * {@link \Infrastructure\Finder\EloquentFinder} に {@link \Domain\Common\CarbonRange} による検索機能を追加するためのユーティリティ.
 */
trait EloquentFinderRangeFilter
{
    /**
     * 検索条件：指定したキーに対応する期間（日付）に指定した値が含まれる.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $key
     * @param \Domain\Common\Carbon $value
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function setDateRangeContains(EloquentBuilder $query, string $key, Carbon $value): EloquentBuilder
    {
        return $query
            ->where("{$key}_start", '<=', $value->toDateString())
            ->where("{$key}_end", '>=', $value->toDateString());
    }

    /**
     * 検索条件：指定したキーに対応する期間（整数）に指定した値が含まれる.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $key
     * @param int $value
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function setIntRangeContains(EloquentBuilder $query, string $key, int $value): EloquentBuilder
    {
        return $query->where("{$key}_start", '<=', $value)->where("{$key}_end", '>=', $value);
    }
}
