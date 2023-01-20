<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\ServiceCodeDictionary;

use Domain\ServiceCodeDictionary\DwsVisitingCareForPwsdDictionaryFinder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Infrastructure\Finder\EloquentFinder;
use Infrastructure\Finder\EloquentFinderCarbonFilter;

/**
 * {@link \Domain\ServiceCodeDictionary\DwsVisitingCareForPwsdDictionaryFinder} Eloquent 実装.
 */
final class DwsVisitingCareForPwsdDictionaryFinderEloquentImpl extends EloquentFinder implements DwsVisitingCareForPwsdDictionaryFinder
{
    use EloquentFinderCarbonFilter;

    /** {@inheritdoc} */
    protected function getQueryBuilder(): EloquentBuilder
    {
        return DwsVisitingCareForPwsdDictionary::query();
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore NOTE: キーワード検索をしないためignoreとする
     */
    protected function baseTableName(): string
    {
        return DwsVisitingCareForPwsdDictionary::TABLE;
    }

    /** {@inheritdoc} */
    protected function setCondition(EloquentBuilder $query, string $key, $value): EloquentBuilder
    {
        switch ($key) {
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
