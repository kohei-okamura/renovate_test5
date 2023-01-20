<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Shift;

use Domain\Shift\Attendance as DomainAttendance;
use Domain\Shift\AttendanceRepository;
use Infrastructure\Repository\EloquentRepository;
use ScalikePHP\Seq;

/**
 * AttendanceRepository eloquent implementation.
 */
final class AttendanceRepositoryEloquentImpl extends EloquentRepository implements AttendanceRepository
{
    /** {@inheritdoc} */
    protected function lookupHandler(int ...$ids): Seq
    {
        $xs = Attendance::findMany($ids);
        return Seq::fromArray($xs)->map(fn (Attendance $x): DomainAttendance => $x->toDomain());
    }

    /** {@inheritdoc} */
    protected function storeInTransaction(mixed $entity): DomainAttendance
    {
        assert($entity instanceof DomainAttendance);
        $x = Attendance::fromDomain($entity);
        $x->save();
        if ($x->assignees()->exists()) {
            $x->assignees()->delete();
        }
        $assignees = AttendanceAssignee::domainAssigneesToAttendanceAssignees($x->id, $entity->assignees);
        $x->assignees()->saveMany($assignees);
        if ($x->durations()->exists()) {
            $x->durations()->delete();
        }
        $durations = AttendanceDuration::domainDurationsToAttendanceDurations($x->id, $entity->durations);
        $x->durations()->saveMany($durations);
        $x->syncServiceOptions($entity->options);
        return $x->toDomain();
    }

    /** {@inheritdoc} */
    protected function removeByIdInTransaction(int ...$ids): void
    {
        Attendance::destroy($ids);
    }
}
