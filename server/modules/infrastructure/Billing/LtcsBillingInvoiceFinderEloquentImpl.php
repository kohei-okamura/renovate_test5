<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Billing;

use Domain\Billing\LtcsBillingInvoiceFinder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Infrastructure\Finder\EloquentFinder;

/**
 * {@link \Domain\Billing\LtcsBillingInvoice} Finder Eloquent Impl.
 */
class LtcsBillingInvoiceFinderEloquentImpl extends EloquentFinder implements LtcsBillingInvoiceFinder
{
    /** {@inheritdoc} */
    protected function getQueryBuilder(): EloquentBuilder
    {
        return LtcsBillingInvoice::query();
    }

    /** {@inheritdoc} */
    protected function setCondition(EloquentBuilder $query, string $key, $value): EloquentBuilder
    {
        switch ($key) {
            case 'billingId':
                return $query->where('billing_id', '=', $value);
            case 'bundleId':
                return $query->where('bundle_id', '=', $value);
            default:
                return $query;
        }
    }
}
