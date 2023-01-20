<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Project;

use Domain\Project\DwsProjectFinder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Infrastructure\Finder\EloquentFinder;

/**
 * {@link \Domain\Project\DwsProjectFinder} Eloquent 実装.
 */
final class DwsProjectFinderEloquentImpl extends EloquentFinder implements DwsProjectFinder
{
    /** {@inheritdoc} */
    protected function columns(): array
    {
        return ['dws_project.*'];
    }

    /** {@inheritdoc} */
    protected function getQueryBuilder(): EloquentBuilder
    {
        return DwsProject::query()
            ->join(
                'dws_project_to_attr',
                'dws_project.id',
                '=',
                'dws_project_to_attr.dws_project_id'
            )
            ->join(
                'dws_project_attr',
                'dws_project_attr.id',
                '=',
                'dws_project_to_attr.dws_project_attr_id'
            );
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore NOTE: キーワード検索をしないためignoreとする
     */
    protected function baseTableName(): string
    {
        return DwsProject::TABLE;
    }

    /**
     * クエリビルダーに検索条件を設定する.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $key
     * @param mixed $value
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function setCondition(EloquentBuilder $query, string $key, $value): EloquentBuilder
    {
        switch ($key) {
            case 'officeIds':
                return $query->whereIn('office_id', is_array($value) ? $value : [$value]);
            case 'userId':
                return $query->where('user_id', '=', $value);
            default:
                return parent::setCondition($query, $key, $value);
        }
    }
}
