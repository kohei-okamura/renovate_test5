<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\ServiceCodeDictionary;

use Domain\ServiceCodeDictionary\DwsHomeHelpServiceDictionary;
use Domain\ServiceCodeDictionary\DwsHomeHelpServiceDictionaryEntry as DomainEntry;
use Domain\ServiceCodeDictionary\DwsHomeHelpServiceDictionaryEntryFinder;
use Domain\ServiceCodeDictionary\DwsServiceCodeCategory;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Infrastructure\Finder\EloquentFinder;
use Infrastructure\Finder\EloquentFinderBooleanFilter;
use Lib\Exceptions\SetupException;
use ScalikePHP\Option;

/**
 * {@link \Domain\ServiceCodeDictionary\DwsHomeHelpServiceDictionaryEntryFinder} Eloquent 実装.
 */
final class DwsHomeHelpServiceDictionaryEntryFinderEloquentImpl extends EloquentFinder /** implements DwsHomeHelpServiceDictionaryEntryFinder */
{
    use EloquentFinderBooleanFilter;

    /** {@inheritdoc} */
    public function findByCategory(
        DwsHomeHelpServiceDictionary $dictionary,
        DwsServiceCodeCategory $category
    ): DomainEntry {
        return $this->findByCategoryOption($dictionary, $category)->getOrElse(function () use ($category): void {
            throw new SetupException("DwsHomeHelpServiceDictionaryEntry(category = {$category}) not found");
        });
    }

    /** {@inheritdoc} */
    public function findByCategoryOption(
        DwsHomeHelpServiceDictionary $dictionary,
        DwsServiceCodeCategory $category
    ): Option {
        $filterParams = [
            'dwsHomeHelpServiceDictionaryId' => $dictionary->id,
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
        return DwsHomeHelpServiceDictionaryEntry::query();
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore NOTE: キーワード検索をしないためignoreとする
     */
    protected function baseTableName(): string
    {
        return DwsHomeHelpServiceDictionaryEntry::TABLE;
    }

    /** {@inheritdoc} */
    protected function setCondition(EloquentBuilder $query, string $key, $value): EloquentBuilder
    {
        switch ($key) {
            case 'category':
                assert($value instanceof DwsServiceCodeCategory);
                return $query->where('category', '=', $value->value());
            case 'dwsHomeHelpServiceDictionaryId':
                return $query->where('dws_home_help_service_dictionary_id', '=', $value);
            case 'isSecondary':
                return $this->setBooleanCondition($query, 'is_secondary', $value);
            case 'isExtra':
                return $this->setBooleanCondition($query, 'is_extra', $value);
            case 'isPlannedByNovice':
                return $this->setBooleanCondition($query, 'is_planned_by_novice', $value);
            case 'providerType':
                return $query->where('provider_type', '=', $value);
            case 'morningDuration':
                return $query
                    ->where('morning_duration_start', '<', $value)
                    ->where('morning_duration_end', '>=', $value);
            case 'daytimeDuration':
                return $query
                    ->where('dayTime_duration_start', '<', $value)
                    ->where('dayTime_duration_end', '>=', $value);
            case 'nightDuration':
                return $query
                    ->where('night_duration_start', '<', $value)
                    ->where('night_duration_end', '>=', $value);
            case 'midnightDuration1':
                return $query
                    ->where('midnight_duration1_start', '<', $value)
                    ->where('midnight_duration1_end', '>=', $value);
            case 'midnightDuration2':
                return $query
                    ->where('midnight_duration2_start', '<', $value)
                    ->where('midnight_duration2_end', '>=', $value);
            case 'serviceCodes':
                assert(is_array($value));
                return $query->whereIn('service_code', $value);
            default:
                return $query;
        }
    }
}
