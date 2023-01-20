<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Staff;

use Domain\Staff\StaffFinder;
use Domain\Staff\StaffStatus;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder;
use Infrastructure\Finder\EloquentFinder;
use Infrastructure\Finder\EloquentFinderBooleanFilter;

/**
 * {@link \Domain\Staff\StaffFinder} Eloquent 実装.
 */
class StaffFinderEloquentImpl extends EloquentFinder implements StaffFinder
{
    use EloquentFinderBooleanFilter;

    /** {@inheritdoc} */
    protected function columns(): array
    {
        return ['staff.*'];
    }

    /** {@inheritdoc} */
    protected function getQueryBuilder(): EloquentBuilder
    {
        return Staff::query()
            ->join('staff_to_attr', 'staff_to_attr.staff_id', '=', 'staff.id')
            ->join('staff_attr', 'staff_attr.id', '=', 'staff_to_attr.staff_attr_id');
    }

    /** {@inheritdoc} */
    protected function baseTableName(): string
    {
        return Staff::TABLE;
    }

    /** {@inheritdoc} */
    protected function setCondition(EloquentBuilder $query, string $key, $value): EloquentBuilder
    {
        switch ($key) {
            case 'email':
                return $query->where('email', '=', $value);
            case 'isEnabled':
                return $this->setBooleanCondition($query, 'is_enabled', $value);
            case 'officeId':
                return $query
                    ->join('staff_attr_to_office', 'staff_attr_to_office.staff_attr_id', '=', 'staff_attr.id')
                    ->where('office_id', '=', $value);
            case 'officeIds':
                $ids = is_array($value) ? $value : [$value];
                return $query->whereExists(function (Builder $q) use ($ids): void {
                    $q->from('staff_attr_to_office')
                        ->whereIn('office_id', $ids)
                        ->whereRaw('staff_attr.id = staff_attr_id');
                });
            case 'q':
                $x = trim($value);
                return strlen($x)
                    ? $this->setKeywordCondition($query, preg_split('/\s/u', $x))
                    : $query;
            case 'sex':
                // TODO DEV-6114 列挙型（Sex）ではなくその値を受け取る前提となっている
                return $query->where('sex', '=', $value);
            case 'statuses':
                $values = array_map(function ($x) {
                    assert($x instanceof StaffStatus);
                    return $x->value();
                }, is_array($value) ? $value : [$value]);
                return $query->whereIn('status', $values);
            default:
                return parent::setCondition($query, $key, $value);
        }
    }

    /**
     * クエリビルダーにソート順を設定する.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $sortBy
     * @param bool $desc
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function setSortBy(EloquentBuilder $query, string $sortBy, bool $desc): EloquentBuilder
    {
        switch ($sortBy) {
            case 'name':
                $direction = $desc ? 'desc' : 'asc';
                return $query->orderBy('phonetic_family_name', $direction)->orderBy('phonetic_given_name', $direction);
            default:
                return parent::setSortBy($query, $sortBy, $desc);
        }
    }
}
