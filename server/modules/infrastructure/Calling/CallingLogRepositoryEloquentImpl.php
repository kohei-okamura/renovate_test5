<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Calling;

use Domain\Calling\CallingLog as DomainCallingLog;
use Domain\Calling\CallingLogRepository;
use Infrastructure\Repository\EloquentRepository;
use ScalikePHP\Seq;

/**
 * CallingLogRepository eloquent implementation.
 */
final class CallingLogRepositoryEloquentImpl extends EloquentRepository implements CallingLogRepository
{
    /** {@inheritdoc} */
    protected function lookupHandler(int ...$ids): Seq
    {
        $xs = CallingLog::findMany($ids);
        return Seq::fromArray($xs)->map(fn (CallingLog $x): DomainCallingLog => $x->toDomain());
    }

    /** {@inheritdoc} */
    protected function storeInTransaction(mixed $entity): DomainCallingLog
    {
        assert($entity instanceof DomainCallingLog);
        $calling = CallingLog::fromDomain($entity)->saveIfNotExists();
        return $calling->toDomain();
    }

    /** {@inheritdoc} */
    protected function removeByIdInTransaction(int ...$ids): void
    {
        CallingLog::destroy($ids);
    }
}
