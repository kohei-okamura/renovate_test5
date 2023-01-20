<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\ServiceCodeDictionary;

use Domain\ServiceCodeDictionary\DwsVisitingCareForPwsdDictionary as DomainDwsVisitingCareForPwsdDictionary;
use Domain\ServiceCodeDictionary\DwsVisitingCareForPwsdDictionaryRepository;
use Infrastructure\Repository\EloquentRepository;
use ScalikePHP\Seq;

/**
 * DwsVisitingCareForPwsdDictionaryRepository eloquent implementation.
 */
final class DwsVisitingCareForPwsdDictionaryRepositoryEloquentImpl extends EloquentRepository implements DwsVisitingCareForPwsdDictionaryRepository
{
    /** {@inheritdoc} */
    protected function lookupHandler(int ...$ids): Seq
    {
        $xs = DwsVisitingCareForPwsdDictionary::findMany($ids);
        return Seq::fromArray($xs)
            ->map(fn (DwsVisitingCareForPwsdDictionary $x): DomainDwsVisitingCareForPwsdDictionary => $x->toDomain());
    }

    /** {@inheritdoc} */
    protected function storeInTransaction(mixed $entity): DomainDwsVisitingCareForPwsdDictionary
    {
        assert($entity instanceof DomainDwsVisitingCareForPwsdDictionary);
        $dictionary = DwsVisitingCareForPwsdDictionary::fromDomain($entity);
        $dictionary->save();
        if ($dictionary->entries()->exists()) {
            $dictionary->entries()->delete();
        }
        return $dictionary->toDomain();
    }

    /** {@inheritdoc} */
    protected function removeByIdInTransaction(int ...$ids): void
    {
        DwsVisitingCareForPwsdDictionary::destroy($ids);
    }
}
