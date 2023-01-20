<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Billing;

use Domain\Billing\DwsHomeHelpServiceChunk as DomainChunk;
use Domain\Billing\DwsHomeHelpServiceChunkRepository;
use Infrastructure\Repository\EloquentRepository;
use ScalikePHP\Seq;

/**
 * {@link \Domain\Billing\DwsHomeHelpServiceChunkRepository} Eloquent 実装.
 */
final class DwsHomeHelpServiceChunkRepositoryEloquentImpl extends EloquentRepository implements DwsHomeHelpServiceChunkRepository
{
    /** {@inheritdoc} */
    protected function connection(): string
    {
        return self::CONNECTION_TEMPORARY;
    }

    /** {@inheritdoc} */
    protected function lookupHandler(int ...$ids): Seq
    {
        $xs = DwsHomeHelpServiceChunk::findMany($ids);
        return Seq::fromArray($xs)->map(fn (DwsHomeHelpServiceChunk $x): DomainChunk => $x->toDomain());
    }

    /** {@inheritdoc} */
    protected function storeInTransaction(mixed $entity): DomainChunk
    {
        assert($entity instanceof DomainChunk);
        $homeHelpServiceChunk = DwsHomeHelpServiceChunk::fromDomain($entity);
        $homeHelpServiceChunk->save();
        return $homeHelpServiceChunk->toDomain();
    }

    /** {@inheritdoc} */
    protected function removeByIdInTransaction(int ...$ids): void
    {
        DwsHomeHelpServiceChunk::destroy($ids);
    }
}
