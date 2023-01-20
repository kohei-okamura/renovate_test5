<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\UserBilling;

use Domain\Common\Carbon;
use Domain\UserBilling\WithdrawalTransactionFinder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Infrastructure\Finder\EloquentFinder;

/**
 * {@link \Domain\UserBilling\WithdrawalTransactionFinder} の実装.
 */
final class WithdrawalTransactionFinderEloquentImpl extends EloquentFinder implements WithdrawalTransactionFinder
{
    /** {@inheritdoc} */
    protected function columns(): array
    {
        return ['*'];
    }

    /** {@inheritdoc} */
    protected function getQueryBuilder(): EloquentBuilder
    {
        return WithdrawalTransaction::query();
    }

    /** {@inheritdoc} */
    protected function baseTableName(): string
    {
        return WithdrawalTransaction::TABLE;
    }

    /** {@inheritdoc} */
    protected function setCondition(EloquentBuilder $query, string $key, $value): EloquentBuilder
    {
        switch ($key) {
            case 'deductedOn':
                assert($value instanceof Carbon);
                return $query->where('deducted_on', '=', $value->startOfDay());
            case 'start':
                assert($value instanceof Carbon);
                return $query->where('created_at', '>=', $value->startOfDay());
            case 'end':
                assert($value instanceof Carbon);
                return $query->where('created_at', '<=', $value->endOfDay());
            default:
                return parent::setCondition($query, $key, $value);
        }
    }
}
