<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Fixtures;

use Infrastructure\Shift\Attendance;
use Infrastructure\Shift\AttendanceAssignee;
use Infrastructure\Shift\AttendanceDuration;

/**
 * Attendance fixture.
 *
 * @mixin \Tests\Unit\Examples\ExamplesConsumer
 */
trait AttendanceFixture
{
    /**
     * 勤務実績 登録.
     *
     * @return void
     */
    protected function createAttendances(): void
    {
        foreach ($this->examples->attendances as $entity) {
            $x = Attendance::fromDomain($entity);
            $x->save();
            $assignees = AttendanceAssignee::domainAssigneesToAttendanceAssignees($x->id, $entity->assignees);
            $x->assignees()->saveMany($assignees);
            $durations = AttendanceDuration::domainDurationsToAttendanceDurations($x->id, $entity->durations);
            $x->durations()->saveMany($durations);
            $x->syncServiceOptions($entity->options);
            $x->assignees()->where('sort_order', '>', $entity->headcount)->delete();
        }
    }
}
