<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Billing;

use Domain\Billing\DwsBillingInvoiceFinder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Infrastructure\Finder\EloquentFinder;

/**
 * {@link \Domain\Billing\DwsBillingInvoiceFinder} Eloquent 実装.
 */
final class DwsBillingInvoiceFinderEloquentImpl extends EloquentFinder implements DwsBillingInvoiceFinder
{
    /** {@inheritdoc} */
    protected function getQueryBuilder(): EloquentBuilder
    {
        return DwsBillingInvoice::query();
    }

    /** {@inheritdoc} */
    protected function setCondition(EloquentBuilder $query, string $key, $value): EloquentBuilder
    {
        switch ($key) {
            case 'dwsBillingBundleId':
                return $query->where('dws_billing_bundle_id', '=', $value);
            case 'dwsBillingBundleIds':
                return $query->whereIn('dws_billing_bundle_id', $value);
            default:
                return $query;
        }
    }
}
