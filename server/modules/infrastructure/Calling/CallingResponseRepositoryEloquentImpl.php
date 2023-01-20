<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Calling;

use Domain\Calling\CallingResponse as DomainCallingResponse;
use Domain\Calling\CallingResponseRepository;
use Infrastructure\Repository\EloquentRepository;
use ScalikePHP\Seq;

/**
 * CallingResponseRepository eloquent implementation.
 */
final class CallingResponseRepositoryEloquentImpl extends EloquentRepository implements CallingResponseRepository
{
    /** {@inheritdoc} */
    protected function lookupHandler(int ...$ids): Seq
    {
        $xs = CallingResponse::findMany($ids);
        return Seq::fromArray($xs)->map(fn (CallingResponse $x): DomainCallingResponse => $x->toDomain());
    }

    /** {@inheritdoc} */
    protected function storeInTransaction(mixed $entity): DomainCallingResponse
    {
        assert($entity instanceof DomainCallingResponse);
        $calling = CallingResponse::fromDomain($entity)->saveIfNotExists();
        return $calling->toDomain();
    }

    /** {@inheritdoc} */
    protected function removeByIdInTransaction(int ...$ids): void
    {
        CallingResponse::destroy($ids);
    }
}
