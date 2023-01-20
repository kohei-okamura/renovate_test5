<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Finder;

use Domain\Common\Carbon;
use Domain\Common\CarbonRange;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;

/**
 * {@link \Infrastructure\Finder\EloquentFinder} に {@link \Domain\Common\Carbon} による検索機能を追加するためのユーティリティ.
 */
trait EloquentFinderCarbonFilter
{
    /**
     * 検索条件：指定したカラムが指定した期間（日付）以降である.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $column
     * @param \Domain\Common\Carbon $value
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function setDateAfter(EloquentBuilder $query, string $column, Carbon $value): EloquentBuilder
    {
        return $this->setDateCondition($query, $column, '>=', $value);
    }

    /**
     * 検索条件：指定したカラムが指定した期間（日付）内である.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $column
     * @param \Domain\Common\CarbonRange $value
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function setDateBetween(EloquentBuilder $query, string $column, CarbonRange $value): EloquentBuilder
    {
        return $query->whereBetween($column, [
            $value->start->toDateString(),
            $value->end->toDateString(),
        ]);
    }

    /**
     * 検索条件：指定したカラムが指定した期間（日付）以前である.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $column
     * @param \Domain\Common\Carbon $value
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function setDateBefore(EloquentBuilder $query, string $column, Carbon $value): EloquentBuilder
    {
        return $this->setDateCondition($query, $column, '<=', $value);
    }

    /**
     * 検索条件：指定したカラムが指定した日付である.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $column
     * @param string $operator
     * @param \Domain\Common\Carbon $value
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function setDateCondition(
        EloquentBuilder $query,
        string $column,
        string $operator,
        Carbon $value
    ): EloquentBuilder {
        return $query->where($column, $operator, $value->toDateString());
    }

    /**
     * 検索条件：指定したカラムが指定した期間（日時）内である.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $column
     * @param \Domain\Common\CarbonRange $value
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function setDateTimeBetween(EloquentBuilder $query, string $column, CarbonRange $value): EloquentBuilder
    {
        return $query->whereBetween($column, [$value->start, $value->end]);
    }
}
