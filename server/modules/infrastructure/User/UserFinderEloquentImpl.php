<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\User;

use Domain\Common\Carbon;
use Domain\Common\ServiceSegment;
use Domain\Contract\ContractStatus;
use Domain\User\UserFinder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder;
use Infrastructure\Contract\Contract;
use Infrastructure\Finder\EloquentFinder;
use Infrastructure\Finder\EloquentFinderBooleanFilter;

/**
 * {@link \Domain\User\UserFinder} Eloquent 実装.
 */
final class UserFinderEloquentImpl extends EloquentFinder implements UserFinder
{
    use EloquentFinderBooleanFilter;

    /** {@inheritdoc} */
    protected function columns(): array
    {
        return ['user.*'];
    }

    /** {@inheritdoc} */
    protected function getQueryBuilder(): EloquentBuilder
    {
        return User::query()
            ->join('user_to_attr', 'user_to_attr.user_id', '=', 'user.id')
            ->join('user_attr', 'user_attr.id', '=', 'user_to_attr.user_attr_id');
    }

    /** {@inheritdoc} */
    protected function baseTableName(): string
    {
        return User::TABLE;
    }

    /** {@inheritdoc} */
    protected function setCondition(EloquentBuilder $query, string $key, $value): EloquentBuilder
    {
        switch ($key) {
            case 'isContractingWith':
                assert(is_array($value) && isset($value['officeId'], $value['date'], $value['serviceSegment']));
                return $this->setContractConditionWithMonth(
                    $query,
                    [$value['officeId']],
                    $value['date'],
                    $value['serviceSegment']
                );
            case 'isEnabled':
                return $this->setBooleanCondition($query, 'is_enabled', $value);
            case 'officeId':
                return $this->setContractConditionWithDate($query, [$value], Carbon::today());
            case 'officeIds':
                $officeIds = is_array($value) ? $value : [$value];
                return $this->setContractConditionWithDate($query, $officeIds, Carbon::today());
            case 'q':
                $x = trim($value);
                return strlen($x)
                    ? $this->setKeywordCondition($query, preg_split('/\s/u', $x))
                    : $query;
            case 'sex':
                return $query->where('sex', '=', $value);
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

    /**
     * 事業所IDおよび契約日における条件を設定する.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $officeIds
     * @param \Domain\Common\Carbon $date
     * @param null|\Domain\Common\ServiceSegment $serviceSegment
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function setContractConditionWithDate(
        EloquentBuilder $query,
        array $officeIds,
        Carbon $date,
        ServiceSegment $serviceSegment = null
    ): EloquentBuilder {
        return $query->whereExists(function (Builder $q1) use ($officeIds, $date, $serviceSegment): void {
            $t = Contract::TABLE;
            $q1->from($t)
                ->join("{$t}_to_attr", "{$t}.id", '=', "{$t}_to_attr.{$t}_id")
                ->join("{$t}_attr", "{$t}_to_attr.{$t}_attr_id", '=', "{$t}_attr.id")
                ->whereIn('office_id', $officeIds)
                ->whereRaw('user_id = user.id')
                ->where(function (Builder $q2) use ($t, $date): void {
                    $q2->where("{$t}_attr.status", '=', ContractStatus::provisional())
                        ->orWhere(function (Builder $q3) use ($date): void {
                            $q3->where('status', '=', ContractStatus::formal())
                                ->where('contracted_on', '<=', $date);
                        });
                });
            if ($serviceSegment !== null) {
                $q1->where('service_segment', $serviceSegment);
            }
        });
    }

    /**
     * 事業所IDおよび契約月における条件を設定する.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $officeIds
     * @param \Domain\Common\Carbon $month
     * @param null|\Domain\Common\ServiceSegment $serviceSegment
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function setContractConditionWithMonth(
        EloquentBuilder $query,
        array $officeIds,
        Carbon $month,
        ServiceSegment $serviceSegment = null
    ): EloquentBuilder {
        return $query->whereExists(function (Builder $q1) use ($officeIds, $month, $serviceSegment): void {
            $t = Contract::TABLE;
            $q1->from($t)
                ->join("{$t}_to_attr", "{$t}.id", '=', "{$t}_to_attr.{$t}_id")
                ->join("{$t}_attr", "{$t}_to_attr.{$t}_attr_id", '=', "{$t}_attr.id")
                ->whereIn('office_id', $officeIds)
                ->whereRaw('user_id = user.id')
                ->where(function (Builder $q2) use ($t, $month): void {
                    $q2->where("{$t}_attr.status", '=', ContractStatus::provisional())
                        ->orWhere(function (Builder $q3) use ($month): void {
                            $q3->whereIn('status', [ContractStatus::formal(), ContractStatus::terminated()])
                                ->where('contracted_on', '<', $month->endOfMonth())
                                ->where(function (Builder $q4) use ($month): void {
                                    $q4->where('terminated_on', '>=', $month->startOfMonth())
                                        ->orWhereNull('terminated_on');
                                });
                        });
                });
            if ($serviceSegment !== null) {
                $q1->where('service_segment', $serviceSegment);
            }
        });
    }
}
