<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Billing;

use Domain\Billing\DwsVisitingCareForPwsdChunk as DomainChunk;
use Domain\Billing\DwsVisitingCareForPwsdChunkRepository;
use Infrastructure\Repository\EloquentRepository;
use ScalikePHP\Seq;

/**
 * {@link \Domain\Billing\DwsVisitingCareForPwsdChunkRepository} Eloquent 実装.
 */
final class DwsVisitingCareForPwsdChunkRepositoryEloquentImpl extends EloquentRepository implements DwsVisitingCareForPwsdChunkRepository
{
    /** {@inheritdoc} */
    protected function connection(): string
    {
        return self::CONNECTION_TEMPORARY;
    }

    /** {@inheritdoc} */
    protected function lookupHandler(int ...$ids): Seq
    {
        $xs = DwsVisitingCareForPwsdChunk::findMany($ids);
        return Seq::fromArray($xs)->map(fn (DwsVisitingCareForPwsdChunk $x): DomainChunk => $x->toDomain());
    }

    /** {@inheritdoc} */
    protected function storeInTransaction(mixed $entity): DomainChunk
    {
        assert($entity instanceof DomainChunk);
        $eloquent = DwsVisitingCareForPwsdChunk::fromDomain($entity);
        $eloquent->save();
        return $eloquent->toDomain();
    }

    /** {@inheritdoc} */
    protected function removeByIdInTransaction(int ...$ids): void
    {
        DwsVisitingCareForPwsdChunk::destroy($ids);
    }
}
