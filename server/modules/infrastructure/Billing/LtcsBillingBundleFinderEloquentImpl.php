<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Billing;

use Domain\Billing\LtcsBillingBundleFinder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Infrastructure\Finder\EloquentFinder;

/**
 * {@link \Domain\Billing\LtcsBillingBundleFinder} Eloquent 実装.
 */
class LtcsBillingBundleFinderEloquentImpl extends EloquentFinder implements LtcsBillingBundleFinder
{
    /** {@inheritdoc} */
    protected function getQueryBuilder(): EloquentBuilder
    {
        return LtcsBillingBundle::query();
    }

    /** {@inheritdoc} */
    protected function setCondition(EloquentBuilder $query, string $key, $value): EloquentBuilder
    {
        switch ($key) {
            case 'billingId':
                return $query->where('billing_id', '=', $value);
            case 'billingIds':
                $values = is_array($value) ? $value : [$value];
                return $query->whereIn('billing_id', $values);
            case 'providedIn':
                return $query->where('provided_in', '=', $value);
            default:
                return $query;
        }
    }
}
