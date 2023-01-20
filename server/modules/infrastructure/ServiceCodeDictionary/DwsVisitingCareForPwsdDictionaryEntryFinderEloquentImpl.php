<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\ServiceCodeDictionary;

use Domain\ServiceCodeDictionary\DwsServiceCodeCategory;
use Domain\ServiceCodeDictionary\DwsVisitingCareForPwsdDictionary;
use Domain\ServiceCodeDictionary\DwsVisitingCareForPwsdDictionaryEntry as DomainEntry;
use Domain\ServiceCodeDictionary\DwsVisitingCareForPwsdDictionaryEntryFinder;
use Domain\ServiceCodeDictionary\Timeframe;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Infrastructure\Finder\EloquentFinder;
use Infrastructure\Finder\EloquentFinderBooleanFilter;
use Lib\Exceptions\SetupException;
use ScalikePHP\Option;

/**
 * {@link \Domain\ServiceCodeDictionary\DwsVisitingCareForPwsdDictionaryEntryFinder} Eloquent 実装.
 */
final class DwsVisitingCareForPwsdDictionaryEntryFinderEloquentImpl extends EloquentFinder /** implements DwsVisitingCareForPwsdDictionaryEntryFinder */
{
    use EloquentFinderBooleanFilter;

    /** {@inheritdoc} */
    public function findByCategory(
        DwsVisitingCareForPwsdDictionary $dictionary,
        DwsServiceCodeCategory $category
    ): DomainEntry {
        return $this->findByCategoryOption($dictionary, $category)->getOrElse(function () use ($category): void {
            throw new SetupException("DwsVisitingCareForPwsdDictionaryEntry(category = {$category}) not found");
        });
    }

    /** {@inheritdoc} */
    public function findByCategoryOption(
        DwsVisitingCareForPwsdDictionary $dictionary,
        DwsServiceCodeCategory $category
    ): Option {
        $filterParams = [
            'dwsVisitingCareForPwsdDictionaryId' => $dictionary->id,
            'category' => $category,
        ];
        $paginationParams = [
            'all' => true,
            'itemsPerPage' => 1,
            'sortBy' => 'id',
        ];
        return $this->find($filterParams, $paginationParams)->list->headOption();
    }

    /** {@inheritdoc} */
    protected function getQueryBuilder(): EloquentBuilder
    {
        return DwsVisitingCareForPwsdDictionaryEntry::query();
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore NOTE: キーワード検索をしないためignoreとする
     */
    protected function baseTableName(): string
    {
        return DwsVisitingCareForPwsdDictionaryEntry::TABLE;
    }

    /** {@inheritdoc} */
    protected function setCondition(EloquentBuilder $query, string $key, $value): EloquentBuilder
    {
        switch ($key) {
            case 'category':
                assert($value instanceof DwsServiceCodeCategory);
                return $query->where('category', '=', $value->value());
            case 'dwsVisitingCareForPwsdDictionaryId':
                return $query->where('dws_visiting_care_for_pwsd_dictionary_id', '=', $value);
            case 'isCoaching':
                return $this->setBooleanCondition($query, 'is_coaching', $value);
            case 'isHospitalized':
                return $this->setBooleanCondition($query, 'is_hospitalized', $value);
            case 'isLongHospitalized':
                return $this->setBooleanCondition($query, 'is_long_hospitalized', $value);
            case 'isSecondary':
                return $this->setBooleanCondition($query, 'is_secondary', $value);
            case 'serviceCodes':
                assert(is_array($value));
                return $query->whereIn('service_code', $value);
            case 'timeframe':
                assert($value instanceof Timeframe);
                return $query->where('timeframe', '=', $value->value());
            default:
                return $query;
        }
    }
}
