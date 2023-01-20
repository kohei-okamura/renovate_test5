<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\ServiceCodeDictionary;

use Domain\Office\HomeVisitLongTermCareSpecifiedOfficeAddition;
use Domain\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionaryEntryFinder;
use Domain\ServiceCodeDictionary\LtcsServiceCodeCategory;
use Domain\ServiceCodeDictionary\Timeframe;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Infrastructure\Finder\EloquentFinder;
use Infrastructure\Finder\EloquentFinderRangeFilter;

/**
 * {@link \Domain\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionaryEntryFinder} Eloquent 実装.
 */
final class LtcsHomeVisitLongTermCareDictionaryEntryFinderEloquentImpl extends EloquentFinder implements LtcsHomeVisitLongTermCareDictionaryEntryFinder
{
    use EloquentFinderRangeFilter;

    /** {@inheritdoc} */
    protected function getQueryBuilder(): EloquentBuilder
    {
        return LtcsHomeVisitLongTermCareDictionaryEntry::query();
    }

    /** {@inheritdoc} */
    protected function baseTableName(): string
    {
        return LtcsHomeVisitLongTermCareDictionaryEntry::TABLE;
    }

    /** {@inheritdoc} */
    protected function setCondition(EloquentBuilder $query, string $key, $value): EloquentBuilder
    {
        switch ($key) {
            case 'category':
                assert($value instanceof LtcsServiceCodeCategory);
                return $query->where('category', '=', $value->value());
            case 'dictionaryId':
                return $query->where('dictionary_id', '=', $value);
            case 'headcount':
                return $query->where('headcount', '=', $value);
            case 'houseworkMinutes':
                return $query
                    ->where('housework_minutes_start', '<', $value)
                    ->where('housework_minutes_end', '>=', $value);
            case 'physicalMinutes':
                return $query
                    ->where('physical_minutes_start', '<', $value)
                    ->where('physical_minutes_end', '>=', $value);
            case 'q': // サービスコードを対象に前方一致検索
                return $query->where('service_code', 'like', "{$value}%");
            case 'serviceCodes':
                assert(is_array($value));
                return $query->whereIn('service_code', $value);
            case 'specifiedOfficeAddition':
                assert($value instanceof HomeVisitLongTermCareSpecifiedOfficeAddition);
                return $query->where('specified_office_addition', '=', $value->value());
            case 'timeframe':
                assert($value instanceof Timeframe);
                return $query->where('timeframe', '=', $value->value());
            case 'totalMinutes':
                return $query
                    ->where('total_minutes_start', '<', $value)
                    ->where('total_minutes_end', '>=', $value);
            default:
                return $query;
        }
    }
}
