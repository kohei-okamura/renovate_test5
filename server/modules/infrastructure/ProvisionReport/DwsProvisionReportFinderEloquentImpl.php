<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\ProvisionReport;

use Domain\Common\Carbon;
use Domain\Common\CarbonRange;
use Domain\ProvisionReport\DwsProvisionReportFinder;
use Domain\ProvisionReport\DwsProvisionReportStatus;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder;
use Infrastructure\Finder\EloquentFinder;
use Infrastructure\Finder\EloquentFinderCarbonFilter;
use Infrastructure\Office\Office;
use Infrastructure\User\User;

/**
 * {@link \Domain\ProvisionReport\DwsProvisionReportFinder} Eloquent 実装.
 */
final class DwsProvisionReportFinderEloquentImpl extends EloquentFinder implements DwsProvisionReportFinder
{
    use EloquentFinderCarbonFilter;

    /** {@inheritdoc} */
    protected function getQueryBuilder(): EloquentBuilder
    {
        return DwsProvisionReport::query();
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore NOTE: キーワード検索をしないためignoreとする
     */
    protected function baseTableName(): string
    {
        return DwsProvisionReport::TABLE;
    }

    /** {@inheritdoc} */
    protected function setCondition(EloquentBuilder $query, string $key, $value): EloquentBuilder
    {
        switch ($key) {
            case 'fixedAt':
                assert($value instanceof CarbonRange);
                return $this->setDateTimeBetween($query, 'fixed_at', $value);
            case 'officeId':
                return $query->where('office_id', '=', $value);
            case 'officeIds':
                $ids = is_array($value) ? $value : [$value];
                return $query->whereIn('office_id', $ids);
            case 'organizationId':
                return $this->setOrganizationCondition($query, $value);
            case 'providedIn':
                assert($value instanceof Carbon);
                return $query->where('provided_in', '=', $value);
            case 'status':
                assert($value instanceof DwsProvisionReportStatus);
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

    /**
     * 利用者・事業所と一致している事業者IDの条件を追加する.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $organizationId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function setOrganizationCondition(
        EloquentBuilder $query,
        int $organizationId
    ): EloquentBuilder {
        return $query
            ->whereExists(function (Builder $q) use ($organizationId): void {
                $base = DwsProvisionReport::TABLE;
                $user = User::TABLE;
                $q->from($user)
                    ->whereRaw("id = {$base}.user_id")
                    ->where('organization_id', $organizationId);
            })
            ->whereExists(function (Builder $q) use ($organizationId): void {
                $base = DwsProvisionReport::TABLE;
                $office = Office::TABLE;
                $q->from($office)
                    ->whereRaw("id = {$base}.office_id")
                    ->where('organization_id', $organizationId);
            });
    }
}
