<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\DwsCertification;

use Domain\DwsCertification\DwsCertificationFinder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Infrastructure\Finder\EloquentFinder;
use Infrastructure\Finder\EloquentFinderCarbonFilter;

/**
 * {@link \Domain\DwsCertification\DwsCertificationFinder} Eloquent 実装.
 */
final class DwsCertificationFinderEloquentImpl extends EloquentFinder implements DwsCertificationFinder
{
    use EloquentFinderCarbonFilter;

    /** {@inheritdoc} */
    protected function columns(): array
    {
        return ['dws_certification.*'];
    }

    /** {@inheritdoc} */
    protected function getQueryBuilder(): Builder
    {
        return DwsCertification::query()
            ->join(
                'dws_certification_to_attr',
                'dws_certification.id',
                '=',
                'dws_certification_to_attr.dws_certification_id'
            )
            ->join(
                'dws_certification_attr',
                'dws_certification_to_attr.dws_certification_attr_id',
                '=',
                'dws_certification_attr.id'
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function baseTableName(): string
    {
        return DwsCertification::TABLE;
    }

    /** {@inheritdoc} */
    protected function setCondition(EloquentBuilder $query, string $key, $value): EloquentBuilder
    {
        switch ($key) {
            case 'activatedOnBefore':
                return $this->setDateBefore($query, 'dws_certification_attr.activated_on', $value);
            case 'deactivatedOnAfter':
                return $this->setDateAfter($query, 'dws_certification_attr.deactivated_on', $value);
            case 'effectivatedBefore':
                return $this->setDateBefore($query, 'effectivated_on', $value);
            case 'status':
                return $query->where('status', '=', $value->value());
            case 'userId':
                return $query->where('user_id', '=', $value);
            case 'userIds':
                $ids = is_array($value) ? $value : [$value];
                return $query->whereIn('user_id', $ids);
            default:
                return $query;
        }
    }

    /** {@inheritdoc} */
    protected function getOrderByColumnName(string $orderBy): string
    {
        switch ($orderBy) {
            case 'effectivatedOn':
                return 'effectivated_on';
            case 'updatedAt':
                return 'updated_at';
            default:
                return parent::getOrderByColumnName($orderBy);
        }
    }
}
