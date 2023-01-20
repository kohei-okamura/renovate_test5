<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Office;

use Domain\Office\VisitingCareForPwsdCalcSpecFinder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Infrastructure\Finder\EloquentFinder;
use Infrastructure\Finder\EloquentFinderRangeFilter;

/**
 * {@link \Domain\Office\VisitingCareForPwsdCalcSpecFinder} Eloquent 実装.
 */
final class VisitingCareForPwsdCalcSpecFinderEloquentImpl extends EloquentFinder implements VisitingCareForPwsdCalcSpecFinder
{
    use EloquentFinderRangeFilter;

    /** {@inheritdoc} */
    protected function columns(): array
    {
        return ['visiting_care_for_pwsd_calc_spec.*'];
    }

    /** {@inheritdoc} */
    protected function getQueryBuilder(): Builder
    {
        return VisitingCareForPwsdCalcSpec::query()
            ->join(
                'visiting_care_for_pwsd_calc_spec_to_attr',
                'visiting_care_for_pwsd_calc_spec.id',
                '=',
                'visiting_care_for_pwsd_calc_spec_to_attr.visiting_care_for_pwsd_calc_spec_id'
            )
            ->join(
                'visiting_care_for_pwsd_calc_spec_attr',
                'visiting_care_for_pwsd_calc_spec_to_attr.visiting_care_for_pwsd_calc_spec_attr_id',
                '=',
                'visiting_care_for_pwsd_calc_spec_attr.id'
            );
    }

    /** {@inheritdoc} */
    protected function baseTableName(): string
    {
        return VisitingCareForPwsdCalcSpec::TABLE;
    }

    /** {@inheritdoc} */
    protected function setCondition(EloquentBuilder $query, string $key, $value): EloquentBuilder
    {
        switch ($key) {
            case 'officeId':
                return $query->where('office_id', '=', $value);
            case 'period':
                return $this->setDateRangeContains($query, 'period', $value);
            default:
                return $query;
        }
    }

    /** {@inheritdoc} */
    protected function setSortBy(EloquentBuilder $query, string $sortBy, bool $desc): EloquentBuilder
    {
        if (empty($sortBy)) {
            // 下記の順でソートする
            // 1. 適用期間開始日の降順
            // 2. 適用期間終了日の降順
            // 3. 登録日時（または主キー）の降順
            return $query->orderByDesc('period_start')
                ->orderByDesc('period_end')
                ->orderByDesc('created_at');
        } else {
            return parent::setSortBy($query, $sortBy, $desc);
        }
    }
}
