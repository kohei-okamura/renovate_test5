<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\DwsAreaGrade;

use Domain\DwsAreaGrade\DwsAreaGradeFinder;
use Illuminate\Database\Eloquent\Builder;
use Infrastructure\Finder\EloquentFinder;

/**
 * {@link \Domain\DwsAreaGrade\DwsAreaGradeFinder} Eloquent 実装.
 */
final class DwsAreaGradeFinderEloquentImpl extends EloquentFinder implements DwsAreaGradeFinder
{
    /** {@inheritdoc} */
    protected function getQueryBuilder(): Builder
    {
        return DwsAreaGrade::query();
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore NOTE: キーワード検索をしないためignoreとする
     */
    protected function baseTableName(): string
    {
        return DwsAreaGrade::TABLE;
    }

    /**
     * クエリビルダーにソート順を設定する.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $sortBy
     * @param bool $desc
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function setSortBy(Builder $query, string $sortBy, bool $desc): Builder
    {
        switch ($sortBy) {
            case 'code':
                $direction = $desc ? 'desc' : 'asc';
                return $query->orderBy('code', $direction);
            case 'name':
                $direction = $desc ? 'desc' : 'asc';
                return $query->orderBy('name', $direction);
            default:
                return parent::setSortBy($query, $sortBy, $desc);
        }
    }
}
