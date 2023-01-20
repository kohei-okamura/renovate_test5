<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Staff;

use Domain\FinderResult;
use Domain\Staff\StaffDistanceFinder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Infrastructure\Finder\EloquentDistanceFinderFeature;

/**
 * {@link \Domain\Staff\StaffDistanceFinder} Eloquent 実装.
 */
final class StaffDistanceFinderEloquentImpl extends StaffFinderEloquentImpl implements StaffDistanceFinder
{
    use EloquentDistanceFinderFeature;

    /** {@inheritdoc} */
    public function find(array $filterParams, array $paginationParams): FinderResult
    {
        $this->ensureDistanceFinder($filterParams);
        return parent::find($filterParams, $paginationParams);
    }

    /** {@inheritdoc} */
    protected function setConditions(EloquentBuilder $query, array $filterParams): EloquentBuilder
    {
        $this->setDistanceCondition($query, $filterParams);
        return parent::setConditions($query, $filterParams);
    }
}
