<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\ServiceCodeDictionary;

use Domain\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionaryFinder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Infrastructure\Finder\EloquentFinder;
use Infrastructure\Finder\EloquentFinderCarbonFilter;

/**
 * {@link \Domain\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionaryFinder} Eloquent 実装.
 */
final class LtcsHomeVisitLongTermCareDictionaryFinderEloquentImpl extends EloquentFinder implements LtcsHomeVisitLongTermCareDictionaryFinder
{
    use EloquentFinderCarbonFilter;

    /** {@inheritdoc} */
    protected function getQueryBuilder(): EloquentBuilder
    {
        return LtcsHomeVisitLongTermCareDictionary::query();
    }

    /** {@inheritdoc} */
    protected function baseTableName(): string
    {
        return LtcsHomeVisitLongTermCareDictionary::TABLE;
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
}
