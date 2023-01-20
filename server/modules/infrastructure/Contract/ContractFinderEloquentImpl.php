<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Contract;

use Domain\Common\Carbon;
use Domain\Contract\ContractFinder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Infrastructure\Finder\EloquentFinder;
use Infrastructure\Finder\EloquentFinderCarbonFilter;

/**
 * {@link \Domain\Contract\ContractFinder} Eloquent 実装.
 */
final class ContractFinderEloquentImpl extends EloquentFinder implements ContractFinder
{
    use EloquentFinderCarbonFilter;

    /** {@inheritdoc} */
    protected function columns(): array
    {
        return ['contract.*'];
    }

    /** {@inheritdoc} */
    protected function getQueryBuilder(): EloquentBuilder
    {
        return Contract::query()
            ->join('contract_to_attr', 'contract_to_attr.contract_id', '=', 'contract.id')
            ->join('contract_attr', 'contract_attr.id', '=', 'contract_to_attr.contract_attr_id');
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore NOTE: キーワード検索をしないためignoreとする
     */
    protected function baseTableName(): string
    {
        return Contract::TABLE;
    }

    /**
     * クエリビルダーに検索条件を設定する.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $key
     * @param mixed $value
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function setCondition(EloquentBuilder $query, string $key, $value): EloquentBuilder
    {
        switch ($key) {
            case 'contractedOnAfter':
                return $this->setDateAfter($query, 'contracted_on', $value);
            case 'contractedOnBefore':
            case 'date':
                return $this->setDateBefore($query, 'contracted_on', $value);
            case 'officeId':
                return $query->where('office_id', '=', $value);
            case 'officeIds':
                $ids = is_array($value) ? $value : [$value];
                return $query->whereIn('office_id', $ids);
            case 'serviceSegment':
                return $query->where('service_segment', '=', $value);
            case 'status':
                $status = is_array($value) ? $value : [$value];
                return $query->whereIn('status', $status);
            case 'terminatedIn':
                assert(is_array($value));
                return $query->whereBetween('terminated_on', $value);
            case 'terminatedOnAfter':
                // `terminated_on` が `null` である場合も「未来である」とみなすため NULL の場合も含める.
                assert($value instanceof Carbon);
                return $query->where(function (EloquentBuilder $q) use ($value) {
                    $q->whereNull('terminated_on')->orWhere('terminated_on', '>=', $value);
                });
            case 'userId':
                return $query->where('user_id', '=', $value);
            case 'userIds':
                return $query->whereIn('user_id', $value);
            default:
                return parent::setCondition($query, $key, $value);
        }
    }

    /** {@inheritdoc} */
    protected function getOrderByColumnName(string $orderBy): string
    {
        switch ($orderBy) {
            case 'contractedOn':
                return 'contracted_on';
            case 'updatedAt':
                return 'updated_at';
            default:
                return parent::getOrderByColumnName($orderBy);
        }
    }
}
