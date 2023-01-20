<?php
/**
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\User;

use Domain\User\UserDwsCalcSpecFinder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Infrastructure\Finder\EloquentFinder;
use Infrastructure\Finder\EloquentFinderCarbonFilter;

/**
 * {@link \Domain\User\UserDwsCalcSpecFinder} Eloquent 実装.
 */
final class UserDwsCalcSpecFinderEloquentImpl extends EloquentFinder implements UserDwsCalcSpecFinder
{
    use EloquentFinderCarbonFilter;

    /** {@inheritdoc} */
    protected function columns(): array
    {
        return [UserDwsCalcSpec::TABLE . '.*'];
    }

    /** {@inheritdoc} */
    protected function getQueryBuilder(): EloquentBuilder
    {
        return UserDwsCalcSpec::query()
            ->join(
                'user_dws_calc_spec_to_attr',
                'user_dws_calc_spec_to_attr.user_dws_calc_spec_id',
                '=',
                'user_dws_calc_spec.id'
            )
            ->join(
                'user_dws_calc_spec_attr',
                'user_dws_calc_spec_attr.id',
                '=',
                'user_dws_calc_spec_to_attr.user_dws_calc_spec_attr_id'
            );
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore NOTE: キーワード検索をしないためignoreとする
     */
    protected function baseTableName(): string
    {
        return UserDwsCalcSpec::TABLE;
    }

    /**
     * クエリビルダーに検索条件を設定する.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $key
     * @param mixed $value
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function setCondition(EloquentBuilder $query, string $key, mixed $value): EloquentBuilder
    {
        return match ($key) {
            'effectivatedOnBefore' => $this->setDateBefore($query, 'effectivated_on', $value),
            'userId' => $query->where('user_id', '=', $value),
            default => parent::setCondition($query, $key, $value),
        };
    }

    /** {@inheritdoc} */
    protected function getOrderByColumnName(string $orderBy): string
    {
        return match ($orderBy) {
            'effectivatedOn' => 'effectivated_on',
            'updatedAt' => 'updated_at',
            default => parent::getOrderByColumnName($orderBy),
        };
    }
}
