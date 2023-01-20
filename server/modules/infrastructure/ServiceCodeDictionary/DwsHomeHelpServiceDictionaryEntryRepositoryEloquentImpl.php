<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\ServiceCodeDictionary;

use Domain\ServiceCodeDictionary\DwsHomeHelpServiceDictionaryEntry as DomainDwsHomeHelpServiceDictionaryEntry;
use Domain\ServiceCodeDictionary\DwsHomeHelpServiceDictionaryEntryRepository;
use Infrastructure\Repository\EloquentRepository;
use ScalikePHP\Seq;

/**
 * DwsHomeHelpServiceDictionaryEntryRepository Eloquent Implementation.
 */
class DwsHomeHelpServiceDictionaryEntryRepositoryEloquentImpl extends EloquentRepository implements DwsHomeHelpServiceDictionaryEntryRepository
{
    /** {@inheritdoc} */
    protected function lookupHandler(int ...$ids): Seq
    {
        $xs = DwsHomeHelpServiceDictionaryEntry::findMany($ids);
        return Seq::fromArray($xs)
            ->map(fn (DwsHomeHelpServiceDictionaryEntry $x): DomainDwsHomeHelpServiceDictionaryEntry => $x->toDomain());
    }

    /** {@inheritdoc} */
    protected function storeInTransaction(mixed $entity): DomainDwsHomeHelpServiceDictionaryEntry
    {
        assert($entity instanceof DomainDwsHomeHelpServiceDictionaryEntry);
        $entry = DwsHomeHelpServiceDictionaryEntry::fromDomain($entity);
        $entry->save();
        return $entry->toDomain();
    }

    /** {@inheritdoc} */
    protected function removeByIdInTransaction(int ...$ids): void
    {
        DwsHomeHelpServiceDictionaryEntry::destroy($ids);
    }
}
