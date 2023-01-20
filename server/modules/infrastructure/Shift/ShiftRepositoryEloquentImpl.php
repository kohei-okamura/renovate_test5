<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Shift;

use Domain\Shift\Shift as DomainShift;
use Domain\Shift\ShiftRepository;
use Infrastructure\Repository\EloquentRepository;
use ScalikePHP\Seq;

/**
 * ShiftRepository eloquent implementation.
 */
final class ShiftRepositoryEloquentImpl extends EloquentRepository implements ShiftRepository
{
    /** {@inheritdoc} */
    protected function lookupHandler(int ...$ids): Seq
    {
        $xs = Shift::findMany($ids);
        return Seq::fromArray($xs)->map(fn (Shift $x): DomainShift => $x->toDomain());
    }

    /** {@inheritdoc} */
    protected function storeInTransaction(mixed $entity): DomainShift
    {
        assert($entity instanceof DomainShift);
        $x = Shift::fromDomain($entity);
        $x->save();
        $assignees = ShiftAssignee::domainAssigneesToShiftAssignees($x->id, $entity->assignees);
        $x->assignees()->saveMany($assignees);
        if ($x->durations()->exists()) {
            $x->durations()->delete();
        }
        $durations = ShiftDuration::domainDurationsToShiftDurations($x->id, $entity->durations);
        $x->durations()->saveMany($durations);
        $x->syncServiceOptions($entity->options);
        $x->assignees()->where('sort_order', '>', $entity->headcount)->delete();
        return $x->toDomain();
    }

    /** {@inheritdoc} */
    protected function removeByIdInTransaction(int ...$ids): void
    {
        Shift::destroy($ids);
    }
}
