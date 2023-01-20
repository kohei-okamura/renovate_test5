<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Billing;

use Domain\Billing\DwsHomeHelpServiceChunkFinder;
use Domain\Common\Carbon;
use Domain\ServiceCodeDictionary\DwsHomeHelpServiceBuildingType;
use Domain\ServiceCodeDictionary\DwsServiceCodeCategory;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Infrastructure\Finder\EloquentFinder;
use Infrastructure\Finder\EloquentFinderBooleanFilter;

/**
 * {@link \Domain\Billing\DwsHomeHelpServiceChunkFinder} Eloquent 実装.
 */
final class DwsHomeHelpServiceChunkFinderEloquentImpl extends EloquentFinder implements DwsHomeHelpServiceChunkFinder
{
    use EloquentFinderBooleanFilter;

    /** {@inheritdoc} */
    protected function baseTableName(): string
    {
        return DwsHomeHelpServiceChunk::TABLE;
    }

    /** {@inheritdoc} */
    protected function getQueryBuilder(): EloquentBuilder
    {
        return DwsHomeHelpServiceChunk::query();
    }

    /** {@inheritdoc} */
    protected function setCondition(EloquentBuilder $query, string $key, $value): EloquentBuilder
    {
        switch ($key) {
            case 'category':
                assert($value instanceof DwsServiceCodeCategory);
                return $query->where('category_value', '=', $value->value());
            case 'buildingType':
                assert($value instanceof DwsHomeHelpServiceBuildingType);
                return $query->where('building_type_value', '=', $value->value());
            case 'rangeEndAfter':
                assert($value instanceof Carbon);
                return $query->where('range_end', '>', $value);
            case 'isEmergency':
                return $this->setBooleanCondition($query, 'is_emergency', $value);
            case 'isPlannedByNovice':
                return $this->setBooleanCondition($query, 'is_planned_by_novice', $value);
            case 'isFirst':
                return $this->setBooleanCondition($query, 'is_first', $value);
            case 'isWelfareSpecialistCooperation':
                return $this->setBooleanCondition($query, 'is_welfare_specialist_cooperation', $value);
            case 'rangeStartBefore':
                assert($value instanceof Carbon);
                return $query->where('range_start', '<', $value);
            case 'userId':
                return $query->where('user_id', '=', $value);
            default:
                return $query;
        }
    }
}
