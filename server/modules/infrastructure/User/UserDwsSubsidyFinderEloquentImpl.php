<?php

declare(strict_types=1);
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */

namespace Infrastructure\User;

use Domain\User\UserDwsSubsidyFinder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Infrastructure\Finder\EloquentFinder;
use Infrastructure\Finder\EloquentFinderRangeFilter;

/**
 * {@link \Domain\User\UserDwsSubsidyFinder} Eloquent 実装.
 */
final class UserDwsSubsidyFinderEloquentImpl extends EloquentFinder implements UserDwsSubsidyFinder
{
    use EloquentFinderRangeFilter;

    /** {@inheritdoc} */
    protected function columns(): array
    {
        return ['user_dws_subsidy.*'];
    }

    /** {@inheritdoc} */
    protected function baseTableName(): string
    {
        return UserDwsSubsidy::TABLE;
    }

    /** {@inheritdoc} */
    protected function getQueryBuilder(): EloquentBuilder
    {
        return UserDwsSubsidy::query()
            ->join(
                'user_dws_subsidy_to_attr',
                'user_dws_subsidy.id',
                '=',
                'user_dws_subsidy_to_attr.user_dws_subsidy_id'
            )
            ->join(
                'user_dws_subsidy_attr',
                'user_dws_subsidy_attr.id',
                '=',
                'user_dws_subsidy_to_attr.user_dws_subsidy_attr_id'
            );
    }

    /** {@inheritdoc} */
    protected function setCondition(EloquentBuilder $query, string $key, $value): EloquentBuilder
    {
        switch ($key) {
            case 'period':
                return $this->setDateRangeContains($query, 'period', $value);
            case 'userId':
                return $query->where('user_id', '=', $value);
            default:
                return $query;
        }
    }
}
