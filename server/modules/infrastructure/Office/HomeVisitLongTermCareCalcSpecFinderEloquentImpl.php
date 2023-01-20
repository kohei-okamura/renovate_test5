<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Office;

use Domain\Office\HomeVisitLongTermCareCalcSpecFinder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Infrastructure\Finder\EloquentFinder;
use Infrastructure\Finder\EloquentFinderRangeFilter;

/**
 * {@link \Domain\Office\HomeVisitLongTermCareCalcSpecFinder} Eloquent 実装.
 */
final class HomeVisitLongTermCareCalcSpecFinderEloquentImpl extends EloquentFinder implements HomeVisitLongTermCareCalcSpecFinder
{
    use EloquentFinderRangeFilter;

    /** {@inheritdoc} */
    protected function columns(): array
    {
        return ['home_visit_long_term_care_calc_spec.*'];
    }

    /** {@inheritdoc} */
    protected function getQueryBuilder(): Builder
    {
        return HomeVisitLongTermCareCalcSpec::query()
            ->join(
                'home_visit_long_term_care_calc_spec_to_attr',
                'home_visit_long_term_care_calc_spec.id',
                '=',
                'home_visit_long_term_care_calc_spec_to_attr.home_visit_long_term_care_calc_spec_id'
            )
            ->join(
                'home_visit_long_term_care_calc_spec_attr',
                'home_visit_long_term_care_calc_spec_to_attr.home_visit_long_term_care_calc_spec_attr_id',
                '=',
                'home_visit_long_term_care_calc_spec_attr.id'
            );
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore NOTE: キーワード検索をしないためignoreとする
     */
    protected function baseTableName(): string
    {
        return HomeVisitLongTermCareCalcSpec::TABLE;
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
