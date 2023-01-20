<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\ServiceCodeDictionary;

use Domain\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionaryEntry as DomainDictionaryEntry;
use Domain\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionaryEntryRepository;
use Infrastructure\Repository\EloquentRepository;
use ScalikePHP\Seq;

/**
 * {@link \Domain\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionaryEntryRepository} Eloquent 実装.
 */
final class LtcsHomeVisitLongTermCareDictionaryEntryRepositoryEloquentImpl extends EloquentRepository implements LtcsHomeVisitLongTermCareDictionaryEntryRepository
{
    /** {@inheritdoc} */
    protected function lookupHandler(int ...$ids): Seq
    {
        $xs = LtcsHomeVisitLongTermCareDictionaryEntry::findMany($ids);
        return Seq::fromArray($xs)->map(
            fn (LtcsHomeVisitLongTermCareDictionaryEntry $x): DomainDictionaryEntry => $x->toDomain()
        );
    }

    /** {@inheritdoc} */
    protected function storeInTransaction(mixed $entity): DomainDictionaryEntry
    {
        assert($entity instanceof DomainDictionaryEntry);
        $x = LtcsHomeVisitLongTermCareDictionaryEntry::fromDomain($entity);
        $x->save();
        return $x->toDomain();
    }

    /** {@inheritdoc} */
    protected function removeByIdInTransaction(int ...$ids): void
    {
        LtcsHomeVisitLongTermCareDictionaryEntry::destroy($ids);
    }
}
