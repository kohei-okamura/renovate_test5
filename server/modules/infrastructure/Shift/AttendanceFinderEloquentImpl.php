<?php
/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Shift;

use Domain\Shift\AttendanceFinder;
use Domain\Shift\Task;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder;
use Infrastructure\Finder\EloquentFinder;
use Infrastructure\Finder\EloquentFinderBooleanFilter;
use Infrastructure\Finder\EloquentFinderCarbonFilter;

/**
 * {@link \Domain\Shift\AttendanceFinder} Eloquent 実装.
 */
final class AttendanceFinderEloquentImpl extends EloquentFinder implements AttendanceFinder
{
    use EloquentFinderBooleanFilter;
    use EloquentFinderCarbonFilter;

    /** {@inheritdoc} */
    protected function getQueryBuilder(): EloquentBuilder
    {
        return Attendance::query();
    }

    /** {@inheritdoc} */
    protected function setCondition(EloquentBuilder $query, string $key, $value): EloquentBuilder
    {
        switch ($key) {
            case 'assigneeId':
                return $query->whereExists(function (Builder $q) use ($value) {
                    $q->from('attendance_assignee')
                        ->where('staff_id', '=', $value)
                        ->whereRaw('attendance_assignee.attendance_id = attendance.id');
                });
            case 'assignerId':
                return $query->where('assigner_id', '=', $value);
            case 'scheduleDateBefore':
                return $this->setDateBefore($query, 'schedule_date', $value);
            case 'isConfirmed':
                return $this->setBooleanCondition($query, 'is_confirmed', $value);
            case 'officeId':
                return $query->where('office_id', '=', $value);
            case 'officeIds':
                return $query->whereIn('office_id', is_array($value) ? $value : [$value]);
            case 'scheduleDateAfter':
                return $this->setDateAfter($query, 'schedule_date', $value);
            case 'task':
                assert($value instanceof Task);
                return $query->where('task', '=', $value->value());
            case 'tasks':
                return $query->whereIn('task', is_array($value) ? $value : [$value]);
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
        return Attendance::TABLE;
    }

    /** {@inheritdoc} */
    protected function getOrderByColumnName(string $orderBy): string
    {
        switch ($orderBy) {
            case 'userId':
                return 'user_id';
            default:
                return parent::getOrderByColumnName($orderBy);
        }
    }
}
