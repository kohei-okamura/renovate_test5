<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Billing;

use Domain\Billing\DwsBillingBundleFinder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Infrastructure\Finder\EloquentFinder;

/**
 * {@link \Domain\Billing\DwsBillingBundleFinder} Eloquent 実装.
 */
final class DwsBillingBundleFinderEloquentImpl extends EloquentFinder implements DwsBillingBundleFinder
{
    /** {@inheritdoc} */
    protected function getQueryBuilder(): EloquentBuilder
    {
        return DwsBillingBundle::query();
    }

    /** {@inheritdoc} */
    protected function setCondition(EloquentBuilder $query, string $key, $value): EloquentBuilder
    {
        switch ($key) {
            case 'dwsBillingId':
                return $query->where('dws_billing_id', '=', $value);
            case 'dwsBillingIds':
                $values = is_array($value) ? $value : [$value];
                return $query->whereIn('dws_billing_id', $values);
            case 'providedIn':
                return $query->where('provided_in', '=', $value);
            default:
                return $query;
        }
    }
}
