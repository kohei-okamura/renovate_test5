<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\User;

use Domain\User\UserLtcsSubsidyFinder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Infrastructure\Finder\EloquentFinder;
use Infrastructure\Finder\EloquentFinderRangeFilter;

/**
 * {@link \Domain\User\UserLtcsSubsidyFinder} Eloquent 実装.
 */
final class UserLtcsSubsidyFinderEloquentImpl extends EloquentFinder implements UserLtcsSubsidyFinder
{
    use EloquentFinderRangeFilter;

    /** {@inheritdoc} */
    protected function baseTableName(): string
    {
        return UserLtcsSubsidy::TABLE;
    }

    /** {@inheritdoc} */
    protected function columns(): array
    {
        return [UserLtcsSubsidy::TABLE . '.*'];
    }

    /** {@inheritdoc} */
    protected function getQueryBuilder(): EloquentBuilder
    {
        return UserLtcsSubsidy::query()
            ->join(
                'user_ltcs_subsidy_to_attr',
                'user_ltcs_subsidy_to_attr.user_ltcs_subsidy_id',
                '=',
                'user_ltcs_subsidy.id'
            )
            ->join(
                'user_ltcs_subsidy_attr',
                'user_ltcs_subsidy_attr.id',
                '=',
                'user_ltcs_subsidy_to_attr.user_ltcs_subsidy_attr_id'
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
