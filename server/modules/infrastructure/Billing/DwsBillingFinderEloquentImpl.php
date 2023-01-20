<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Billing;

use Domain\Billing\DwsBillingFinder;
use Domain\Billing\DwsBillingStatus;
use Domain\Common\Carbon;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Infrastructure\Finder\EloquentFinder;
use Infrastructure\Finder\EloquentFinderCarbonFilter;

/**
 * {@link \Domain\Billing\DwsBillingFinder} Eloquent 実装.
 */
class DwsBillingFinderEloquentImpl extends EloquentFinder implements DwsBillingFinder
{
    use EloquentFinderCarbonFilter;

    /** {@inheritdoc} */
    protected function getQueryBuilder(): EloquentBuilder
    {
        return DwsBilling::query();
    }

    /** {@inheritdoc} */
    protected function setCondition(EloquentBuilder $query, string $key, $value): EloquentBuilder
    {
        switch ($key) {
            case 'transactedInBefore':
                assert($value instanceof Carbon);
                return $this->setDateBefore($query, 'transacted_in', $value->startOfMonth());
            case 'officeId':
                return $query->where('office_id', '=', $value);
            case 'officeIds':
                $values = is_array($value) ? $value : [$value];
                return $query->whereIn('office_id', $values);
            case 'transactedInAfter':
                assert($value instanceof Carbon);
                return $this->setDateAfter($query, 'transacted_in', $value->startOfMonth());
            case 'status':
                assert($value instanceof DwsBillingStatus);
                return $query->where('status', '=', $value->value());
            case 'statuses':
                $values = array_map(function ($x) {
                    assert($x instanceof DwsBillingStatus);
                    return $x->value();
                }, is_array($value) ? $value : [$value]);
                return $query->whereIn('status', $values);
            case 'transactedIn':
                assert($value instanceof Carbon);
                return $query->where('transacted_in', '=', $value->startOfMonth()->toDateString());
            default:
                return parent::setCondition($query, $key, $value);
        }
    }
}
