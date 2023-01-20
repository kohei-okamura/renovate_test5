<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Shift;

use Domain\Common\Carbon;
use Domain\Shift\ServiceOption;
use Domain\Shift\ShiftFinder;
use Domain\Shift\Task;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder;
use Infrastructure\Finder\EloquentFinder;
use Infrastructure\Finder\EloquentFinderBooleanFilter;
use Infrastructure\Finder\EloquentFinderCarbonFilter;
use ScalikePHP\Seq;

/**
 * {@link \Domain\Shift\ShiftFinder} Eloquent 実装.
 */
final class ShiftFinderEloquentImpl extends EloquentFinder implements ShiftFinder
{
    use EloquentFinderBooleanFilter;
    use EloquentFinderCarbonFilter;

    /** {@inheritdoc} */
    protected function getQueryBuilder(): EloquentBuilder
    {
        return Shift::query();
    }

    /** {@inheritdoc} */
    protected function setCondition(EloquentBuilder $query, string $key, $value): EloquentBuilder
    {
        switch ($key) {
            case 'assigneeId':
                return $query->whereExists(function (Builder $q) use ($value) {
                    $q->from('shift_assignee')
                        ->where('staff_id', '=', $value)
                        ->whereRaw('shift_assignee.shift_id = shift.id');
                });
            case 'assigneeIds':
                // NOTE ALL scan になるので、適切な条件と組み合わせて使うこと
                return $query->whereExists(function (Builder $q) use ($value) {
                    $q->from('shift_assignee')
                        ->whereIn('staff_id', $value)
                        ->whereRaw('shift_assignee.shift_id = shift.id');
                });
            case 'assignerId':
                return $query->where('assigner_id', '=', $value);
            case 'date':
                assert($value instanceof Carbon);
                return $query->where('schedule_date', '=', $value->toDateString());
            case 'scheduleDateBefore':
                return $this->setDateBefore($query, 'schedule_date', $value);
            case 'endDate':
                assert($value instanceof Carbon);
                return $query->whereBetween('schedule_end', [$value->startOfDay(), $value->endOfDay()]);
            case 'excludeOption':
                $excludes = Seq::fromArray($value)->map(fn (ServiceOption $x): int => $x->value())->toArray();
                return $query->whereNotExists(function (Builder $q) use ($excludes) {
                    $q->from('shift_service_option')
                        ->whereIn('service_option', $excludes)
                        ->whereRaw('shift_id = shift.id');
                });
            case 'isCanceled':
                return $this->setBooleanCondition($query, 'is_canceled', $value);
            case 'isConfirmed':
                return $this->setBooleanCondition($query, 'is_confirmed', $value);
            case 'notificationEnabled':
                return $value
                    ? $query->whereExists(function (Builder $q) {
                        $q->from('shift_service_option')
                            ->where('service_option', '=', ServiceOption::notificationEnabled()->value())
                            ->whereRaw('shift_id = shift.id');
                    })
                    : $query;
            case 'officeId':
                return $query->where('office_id', '=', $value);
            case 'officeIds':
                return $query->whereIn('office_id', is_array($value) ? $value : [$value]);
            case 'scheduleStart':
                return $this->setDateTimeBetween($query, 'schedule_start', $value);
            case 'scheduleDateAfter':
                return $this->setDateAfter($query, 'schedule_date', $value);
            case 'startTime':
                assert($value instanceof Carbon);
                return $query->where('schedule_start', '=', $value);
            case 'task':
                assert($value instanceof Task);
                return $query->where('task', '=', $value->value());
            case 'userId':
                return $query->where('user_id', '=', $value);
            default:
                return parent::setCondition($query, $key, $value);
        }
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore NOTE: キーワード検索をしないためignoreとする
     */
    protected function baseTableName(): string
    {
        return Shift::TABLE;
    }

    /** {@inheritdoc} */
    protected function getOrderByColumnName(string $orderBy): string
    {
        switch ($orderBy) {
            case 'userId':
                return 'user_id';
            case 'date':
                return 'schedule_start';
            default:
                return parent::getOrderByColumnName($orderBy);
        }
    }
}
