<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\ServiceCodeDictionary;

use Domain\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionary as DomainDictionary;
use Domain\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionaryRepository;
use Infrastructure\Repository\EloquentRepository;
use ScalikePHP\Seq;

/**
 * {@link \Domain\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionaryRepository} Eloquent 実装.
 */
final class LtcsHomeVisitLongTermCareDictionaryRepositoryEloquentImpl extends EloquentRepository implements LtcsHomeVisitLongTermCareDictionaryRepository
{
    /** {@inheritdoc} */
    protected function lookupHandler(int ...$ids): Seq
    {
        $xs = LtcsHomeVisitLongTermCareDictionary::findMany($ids);
        return Seq::fromArray($xs)->map(
            fn (LtcsHomeVisitLongTermCareDictionary $x): DomainDictionary => $x->toDomain()
        );
    }

    /** {@inheritdoc} */
    protected function storeInTransaction(mixed $entity): DomainDictionary
    {
        assert($entity instanceof DomainDictionary);
        $x = LtcsHomeVisitLongTermCareDictionary::fromDomain($entity);
        $x->save();
        return $x->toDomain();
    }

    /** {@inheritdoc} */
    protected function removeByIdInTransaction(int ...$ids): void
    {
        LtcsHomeVisitLongTermCareDictionary::destroy($ids);
    }
}
