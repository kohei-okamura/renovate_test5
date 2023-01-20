<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Billing;

use Domain\Billing\DwsBillingCopayCoordinationFinder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Infrastructure\Finder\EloquentFinder;

/**
 * {@link \Domain\Billing\DwsBillingCopayCoordinationFinder} Eloquent 実装.
 */
final class DwsBillingCopayCoordinationFinderEloquentImpl extends EloquentFinder implements DwsBillingCopayCoordinationFinder
{
    /** {@inheritdoc} */
    protected function getQueryBuilder(): EloquentBuilder
    {
        return DwsBillingCopayCoordination::query();
    }

    /** {@inheritdoc} */
    protected function setCondition(EloquentBuilder $query, string $key, $value): EloquentBuilder
    {
        switch ($key) {
            case 'dwsBillingId':
                return $query->where('dws_billing_id', '=', $value);
            case 'dwsBillingBundleId':
                return $query->where('dws_billing_bundle_id', '=', $value);
            case 'dwsBillingBundleIds':
                assert(is_array($value));
                return $query->whereIn('dws_billing_bundle_id', $value);
            case 'userIds':
                return $query->whereIn('user_id', is_array($value) ? $value : [$value]);
            default:
                return $query;
        }
    }
}
