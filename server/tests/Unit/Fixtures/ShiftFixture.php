<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Fixtures;

use Infrastructure\Shift\Shift;
use Infrastructure\Shift\ShiftAssignee;
use Infrastructure\Shift\ShiftDuration;

/**
 * Shift fixture.
 *
 * @mixin \Tests\Unit\Examples\ExamplesConsumer
 */
trait ShiftFixture
{
    /**
     * 勤務シフト 登録.
     *
     * @return void
     */
    protected function createShifts(): void
    {
        foreach ($this->examples->shifts as $entity) {
            $x = Shift::fromDomain($entity);
            $x->save();
            $assignees = ShiftAssignee::domainAssigneesToShiftAssignees($x->id, $entity->assignees);
            $x->assignees()->saveMany($assignees);
            $durations = ShiftDuration::domainDurationsToShiftDurations($x->id, $entity->durations);
            $x->durations()->saveMany($durations);
            $x->syncServiceOptions($entity->options);
            $x->assignees()->where('sort_order', '>', $entity->headcount)->delete();
        }
    }
}
