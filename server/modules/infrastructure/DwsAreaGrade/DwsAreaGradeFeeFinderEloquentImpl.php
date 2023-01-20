<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\DwsAreaGrade;

use Domain\DwsAreaGrade\DwsAreaGradeFeeFinder;
use Illuminate\Database\Eloquent\Builder;
use Infrastructure\Finder\EloquentFinder;
use Infrastructure\Finder\EloquentFinderCarbonFilter;

/**
 * {@link \Domain\DwsAreaGrade\DwsAreaGradeFeeFinder} Eloquent 実装.
 */
final class DwsAreaGradeFeeFinderEloquentImpl extends EloquentFinder implements DwsAreaGradeFeeFinder
{
    use EloquentFinderCarbonFilter;

    /** {@inheritdoc} */
    protected function getQueryBuilder(): Builder
    {
        return DwsAreaGradeFee::query();
    }

    /** {@inheritdoc} */
    protected function baseTableName(): string
    {
        return DwsAreaGradeFee::TABLE;
    }

    /** {@inheritdoc} */
    protected function setCondition(Builder $query, string $key, $value): Builder
    {
        switch ($key) {
            case 'dwsAreaGradeId':
                return $query->where('dws_area_grade_id', '=', $value);
            case 'effectivatedBefore':
                return $this->setDateBefore($query, 'effectivated_on', $value);
            default:
                return $query;
        }
    }

    /** {@inheritdoc} */
    protected function getOrderByColumnName(string $orderBy): string
    {
        switch ($orderBy) {
            case 'effectivatedOn':
                return 'effectivated_on';
            default:
                return parent::getOrderByColumnName($orderBy);
        }
    }
}
