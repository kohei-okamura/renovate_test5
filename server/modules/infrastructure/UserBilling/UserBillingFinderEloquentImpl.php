<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\UserBilling;

use Domain\Common\Carbon;
use Domain\User\PaymentMethod;
use Domain\UserBilling\UserBillingFinder;
use Domain\UserBilling\UserBillingResult;
use Domain\UserBillingUsedService\UserBillingUsedService;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder;
use Infrastructure\Finder\EloquentFinder;
use Infrastructure\Finder\EloquentFinderBooleanFilter;

/**
 * {@link \Domain\UserBilling\UserBillingFinder} Eloquent 実装.
 */
final class UserBillingFinderEloquentImpl extends EloquentFinder implements UserBillingFinder
{
    use EloquentFinderBooleanFilter;

    /** {@inheritdoc} */
    protected function columns(): array
    {
        return ['*'];
    }

    /** {@inheritdoc} */
    protected function getQueryBuilder(): EloquentBuilder
    {
        return UserBilling::query();
    }

    /** {@inheritdoc} */
    protected function baseTableName(): string
    {
        return UserBilling::TABLE;
    }

    /** {@inheritdoc} */
    protected function setCondition(EloquentBuilder $query, string $key, $value): EloquentBuilder
    {
        switch ($key) {
            case 'contractNumber':
                return $query->where('user_billing_destination_contract_number', '=', $value);
            case 'isDeposited':
                return $value
                    ? $query->whereNotNull('deposited_at')
                    : $query->whereNull('deposited_at');
            case 'isTransacted':
                return $value
                    ? $query->whereNotNull('transacted_at')
                    : $query->whereNull('transacted_at');
            case 'officeId':
                return $query->where('office_id', '=', $value);
            case 'officeIds':
                return $query->whereIn('office_id', is_array($value) ? $value : [$value]);
            case 'providedIn':
                assert($value instanceof Carbon);
                return $query->where('provided_in', '=', $value->startOfMonth()->toDateString());
            case 'issuedIn':
                assert($value instanceof Carbon);
                return $query->whereBetween('issued_on', [$value->startOfMonth(), $value->endOfMonth()]);
            case 'paymentMethod':
                assert($value instanceof PaymentMethod);
                return $query->where('user_billing_destination_payment_method', '=', $value->value());
            case 'usedService':
                assert($value instanceof UserBillingUsedService);
                if ($value === UserBillingUsedService::disabilitiesWelfareService()) {
                    return $query->whereNotNull('dws_billing_statement_id');
                } elseif ($value === UserBillingUsedService::longTermCareService()) {
                    return $query->whereNotNull('ltcs_billing_statement_id');
                } else {
                    return $query->whereExists(
                        function (Builder $q): void {
                            $q->from('user_billing_other_item')
                                ->whereRaw('user_billing_other_item.user_billing_id = user_billing.id');
                        }
                    );
                }
                // no break
            case 'result':
                assert($value instanceof UserBillingResult);
                return $query->where('result', '=', $value->value());
            case 'userId':
                return $query->where('user_id', '=', $value);
            case 'withdrawalResultCode':
                return $query->where('withdrawal_result_code', '=', $value);
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
                return $query->orderBy('user_phonetic_family_name', $direction)->orderBy('user_phonetic_given_name', $direction);
            default:
                return parent::setSortBy($query, $sortBy, $desc);
        }
    }
}
