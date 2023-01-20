<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Billing;

use Domain\Billing\DwsBillingStatementFinder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Infrastructure\Finder\EloquentFinder;

/**
 * {@link \Domain\Billing\DwsBillingStatementFinder} Eloquent 実装.
 */
final class DwsBillingStatementFinderEloquentImpl extends EloquentFinder implements DwsBillingStatementFinder
{
    /** {@inheritdoc} */
    protected function getQueryBuilder(): EloquentBuilder
    {
        return DwsBillingStatement::query();
    }

    /** {@inheritdoc} */
    protected function setCondition(EloquentBuilder $query, string $key, $value): EloquentBuilder
    {
        switch ($key) {
            case 'dwsBillingBundleId':
                return $query->where('dws_billing_bundle_id', '=', $value);
            case 'dwsBillingBundleIds':
                assert(is_array($value));
                return $query->whereIn('dws_billing_bundle_id', $value);
            case 'dwsCertificationId':
                return $query->where('user_dws_certification_id', $value);
            case 'userId':
                return $query->where('user_id', '=', $value);
            default:
                return $query;
        }
    }
}
