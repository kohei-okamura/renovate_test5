<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\ServiceCodeDictionary;

use Domain\ServiceCodeDictionary\DwsHomeHelpServiceDictionary as DomainDwsHomeHelpServiceDictionary;
use Domain\ServiceCodeDictionary\DwsHomeHelpServiceDictionaryRepository;
use Infrastructure\Repository\EloquentRepository;
use ScalikePHP\Seq;

/**
 * DwsHomeHelpServiceDictionaryRepository eloquent implementation.
 */
final class DwsHomeHelpServiceDictionaryRepositoryEloquentImpl extends EloquentRepository implements DwsHomeHelpServiceDictionaryRepository
{
    /** {@inheritdoc} */
    protected function lookupHandler(int ...$ids): Seq
    {
        $xs = DwsHomeHelpServiceDictionary::findMany($ids);
        return Seq::fromArray($xs)
            ->map(fn (DwsHomeHelpServiceDictionary $x): DomainDwsHomeHelpServiceDictionary => $x->toDomain());
    }

    /** {@inheritdoc} */
    protected function storeInTransaction(mixed $entity): DomainDwsHomeHelpServiceDictionary
    {
        assert($entity instanceof DomainDwsHomeHelpServiceDictionary);
        $dictionary = DwsHomeHelpServiceDictionary::fromDomain($entity);
        $dictionary->save();
        if ($dictionary->entries()->exists()) {
            $dictionary->entries()->delete();
        }
        return $dictionary->toDomain();
    }

    /** {@inheritdoc} */
    protected function removeByIdInTransaction(int ...$ids): void
    {
        DwsHomeHelpServiceDictionary::destroy($ids);
    }
}
