<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Office;

use Domain\Common\Carbon;
use Domain\Contract\ContractStatus;
use Domain\Office\OfficeFinder;
use Domain\Office\OfficeQualification;
use Domain\Office\Purpose;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder;
use Infrastructure\Contract\Contract;
use Infrastructure\Finder\EloquentFinder;
use ScalikePHP\Seq;

/**
 * {@link \Domain\Office\OfficeFinder} Eloquent 実装.
 */
final class OfficeFinderEloquentImpl extends EloquentFinder implements OfficeFinder
{
    /** {@inheritdoc} */
    protected function columns(): array
    {
        return ['office.*'];
    }

    /** {@inheritdoc} */
    protected function getQueryBuilder(): EloquentBuilder
    {
        return Office::query()
            ->join('office_to_attr', 'office_to_attr.office_id', '=', 'office.id')
            ->join('office_attr', 'office_attr.id', '=', 'office_to_attr.office_attr_id');
    }

    /** {@inheritdoc} */
    protected function baseTableName(): string
    {
        return Office::TABLE;
    }

    /** {@inheritdoc} */
    protected function setCondition(EloquentBuilder $query, string $key, $value): EloquentBuilder
    {
        switch ($key) {
            case 'isCommunityGeneralSupportCenter':
                // 事業所番号が XX0XX0001X 〜 XX0XX4999X の範囲のものが地域支援包括センター
                return $value
                    ? $query->whereExists(function (Builder $q): void {
                        $q->from('office_ltcs_prevention_service')
                            // XX0XX0000X 〜 XX0XX4999X
                            ->whereRaw("code REGEXP BINARY '\\\\A\\\\d{2}0\\\\d{2}[01234]\\\\d{4}\\\\z'")
                            // XX0XX0000X は除く
                            ->whereRaw("code REGEXP BINARY '\\\\A\\\\d{2}0\\\\d{2}(?:(?!0000).)*?\\\\d{1}\\\\z'")
                            ->whereRaw('office_ltcs_prevention_service.office_attr_id = office_attr.id');
                    })
                    // 「地域支援包括センターでない」で使いたい場面はないため、とりあえず false が指定されてもフィルタしない
                    : $query;
            case 'officeIds':
                return $query->whereIn('office.id', is_array($value) ? $value : [$value]);
            case 'officeIdsOrExternal':
                return $query->where(function ($query) use ($value): void {
                    $query
                        ->whereIn('office.id', is_array($value) ? $value : [$value])
                        ->orWhere('purpose', '=', Purpose::external()->value());
                });
            case 'officeGroupIds':
                return $query->whereIn('office_group_id', is_array($value) ? $value : [$value]);
            case 'prefecture':
                // TODO DEV-6114 列挙型（Prefecture）ではなくその値を受け取る前提となっている
                return $query->where('addr_prefecture', '=', $value);
            case 'purpose':
                assert($value instanceof Purpose);
                return $query->where('purpose', '=', $value->value());
            case 'q':
                $x = trim($value);
                return strlen($x)
                    ? $this->setKeywordCondition($query, preg_split('/\s/u', $x))
                    : $query;
            case 'qualifications':
                $values = Seq::fromArray($value)->map(fn (OfficeQualification $x): string => $x->value())->toArray();
                return $query->whereExists(function (Builder $q) use ($values): void {
                    $q->from('office_attr_office_qualification')
                        ->whereIn('qualification', $values)
                        ->whereRaw('office_attr_id = office_attr.id');
                });
            case 'statuses':
                return $query->whereIn('status', is_array($value) ? $value : [$value]);
            case 'userId':
                return $this->setContractCondition($query, $value);
            default:
                return parent::setCondition($query, $key, $value);
        }
    }

    /** {@inheritdoc} */
    protected function getOrderByColumnName(string $orderBy): string
    {
        switch ($orderBy) {
            case 'name':
                return 'name';
            default:
                return parent::getOrderByColumnName($orderBy);
        }
    }

    /**
     * 利用者IDの条件を設定する.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $userId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function setContractCondition(
        EloquentBuilder $query,
        int $userId
    ): EloquentBuilder {
        return $query->whereExists(function (Builder $q1) use ($userId): void {
            $t = Contract::TABLE;
            $q1->from($t)
                ->join("{$t}_to_attr", "{$t}.id", '=', "{$t}_to_attr.{$t}_id")
                ->join("{$t}_attr", "{$t}_to_attr.{$t}_attr_id", '=', "{$t}_attr.id")
                ->where('user_id', $userId)
                ->whereRaw('office_id = office.id')
                ->where(function (Builder $q2) use ($t): void {
                    $q2->where("{$t}_attr.status", '=', ContractStatus::provisional())
                        ->orWhere(function (Builder $q3): void {
                            $q3->where('status', '=', ContractStatus::formal())
                                ->where('contracted_on', '<=', Carbon::today());
                        });
                });
        });
    }
}
