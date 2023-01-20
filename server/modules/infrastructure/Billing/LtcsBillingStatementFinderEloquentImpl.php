<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Billing;

use Domain\Billing\LtcsBillingStatementFinder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Infrastructure\Finder\EloquentFinder;

/**
 * {@link \Domain\Billing\LtcsBillingStatementFinder} Eloquent 実装.
 */
final class LtcsBillingStatementFinderEloquentImpl extends EloquentFinder implements LtcsBillingStatementFinder
{
    /** {@inheritdoc} */
    protected function getQueryBuilder(): EloquentBuilder
    {
        return LtcsBillingStatement::query();
    }

    /** {@inheritdoc} */
    protected function setCondition(EloquentBuilder $query, string $key, $value): EloquentBuilder
    {
        switch ($key) {
            case 'billingId':
                return $query->where('billing_id', '=', $value);
            case 'bundleId':
                return $query->where('bundle_id', '=', $value);
            case 'bundleIds':
                assert(is_array($value));
                return $query->whereIn('bundle_id', $value);
            case 'userId':
                return $query->where('user_id', '=', $value);
            default:
                return $query;
        }
    }
}
