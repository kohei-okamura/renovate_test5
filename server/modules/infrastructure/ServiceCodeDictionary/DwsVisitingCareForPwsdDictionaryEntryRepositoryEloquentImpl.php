<?php

declare(strict_types=1);
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */

namespace Infrastructure\ServiceCodeDictionary;

use Domain\ServiceCodeDictionary\DwsVisitingCareForPwsdDictionaryEntry as DomainEntry;
use Domain\ServiceCodeDictionary\DwsVisitingCareForPwsdDictionaryEntryRepository;
use Infrastructure\Repository\EloquentRepository;
use ScalikePHP\Seq;

/**
 * DwsVisitingCareForPwsdDictionaryEntryRepository Eloquent Implementation.
 */
class DwsVisitingCareForPwsdDictionaryEntryRepositoryEloquentImpl extends EloquentRepository implements DwsVisitingCareForPwsdDictionaryEntryRepository
{
    /** {@inheritdoc} */
    protected function lookupHandler(int ...$ids): Seq
    {
        $xs = DwsVisitingCareForPwsdDictionaryEntry::findMany($ids);
        return Seq::fromArray($xs)
            ->map(fn (DwsVisitingCareForPwsdDictionaryEntry $x): DomainEntry => $x->toDomain());
    }

    /** {@inheritdoc} */
    protected function storeInTransaction(mixed $entity): DomainEntry
    {
        assert($entity instanceof DomainEntry);
        $entry = DwsVisitingCareForPwsdDictionaryEntry::fromDomain($entity);
        $entry->save();
        return $entry->toDomain();
    }

    /** {@inheritdoc} */
    protected function removeByIdInTransaction(int ...$ids): void
    {
        DwsVisitingCareForPwsdDictionaryEntry::destroy($ids);
    }
}
